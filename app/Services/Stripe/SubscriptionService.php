<?php

namespace App\Services\Stripe;

use App\Contracts\PromotionCodeProvider;
use App\Contracts\SubscriptionProvider;
use App\Enums\PlanInterval;
use App\Enums\SubscriptionIntent;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PlanCancelledNotification;
use App\Notifications\PlanChangedNotification;
use App\Notifications\PlanResumedNotification;
use App\Notifications\PlanSubscribedNotification;
use App\Support\ServiceResult;
use Laravel\Cashier\Subscription;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class SubscriptionService implements SubscriptionProvider
{
    public function __construct(
        protected PromotionCodeProvider $promotionCodes,
    ) {}

    /**
     * Start a subscription and hand back the Stripe intent the client confirms
     * against. Cashier creates the subscription as incomplete, so nothing is
     * charged and no entitlement is granted here. Everything that follows from
     * a successful payment happens in sync().
     */
    public function start(User $user, Plan $plan, PlanInterval $interval, ?string $promotionCode = null): ServiceResult
    {
        $existing = $user->subscription();

        if ($existing && $existing->valid()) {
            return ServiceResult::error('already_subscribed');
        }

        /**
         * TODO: Past due and unpaid are billing failures on a subscription that
         * already exists. Stripe keeps that subscription and its open invoice,
         * so the fix is to attach a new payment method and retry the invoice,
         * not to open a second subscription alongside the failing one. Until
         * that endpoint exists the request is rejected here.
         */
        if ($existing && ($existing->pastDue() || $existing->stripe_status === StripeSubscription::STATUS_UNPAID)) {
            return ServiceResult::error('payment_required');
        }

        /**
         * A returning or refreshing user still has their unpaid subscription
         * from the last attempt. The same price can be confirmed as it stands,
         * a different one cannot, because its invoice was already drawn against
         * the old price.
         */
        try {
            if ($existing && $existing->incomplete()) {
                if ($existing->stripe_price === $plan->priceId($interval)) {
                    return ServiceResult::success($this->intent($existing));
                }

                $existing->cancelNow();
            }
        } catch (ApiErrorException) {
            return ServiceResult::error('provider_unavailable');
        }

        $promotionCodeId = null;

        if ($promotionCode) {
            $result = $this->promotionCodes->resolve($promotionCode);

            if (!$result->success) {
                return $result;
            }

            $promotionCodeId = $result->data->id;
        }

        $builder = $user->newSubscription('default', $plan->priceId($interval));

        if ($trialEndsAt = $user->resolveTrialEnd()) {
            $builder->trialUntil($trialEndsAt);
        }

        if ($promotionCodeId) {
            $builder->withPromotionCode($promotionCodeId);
        }

        try {
            $subscription = $builder->create($user->defaultPaymentMethod()?->id);

            return ServiceResult::success($this->intent($subscription));
        } catch (ApiErrorException) {
            return ServiceResult::error('provider_unavailable');
        }
    }

    /**
     * Pull the live state from Stripe and commit it locally. This is the only
     * place a subscription turns into an entitlement, and it runs from both the
     * client after a confirmed payment and the webhook whenever it lands. Every
     * write is idempotent so whichever arrives second finds nothing to do.
     */
    public function sync(User $user): void
    {
        $subscription = $user->subscription();

        if (!$subscription) {
            return;
        }

        $subscription->syncStripeStatus();

        $stripeSubscription = $subscription->asStripeSubscription();

        /**
         * Stripe stamps the card on the subscription only. Cashier reads the
         * card columns off the customer default, so without this promotion the
         * user row keeps a null brand and last four after a successful signup.
         */
        if ($stripeSubscription->default_payment_method) {
            $user->updateDefaultPaymentMethod($stripeSubscription->default_payment_method);
        }

        $user->load('subscriptions');

        if ($subscription->valid()) {
            $user->complimentary_plan_id = null;
        }

        $user->fillPlan()->save();

        $this->announceActivation($user, $subscription);
    }

    /**
     * Swap to a different plan.
     */
    public function swap(User $user, Plan $plan, PlanInterval $interval): Subscription
    {
        $user->subscription()->swap($plan->priceId($interval));

        $user->fillPlan()->save();

        $user->notify(new PlanChangedNotification($plan));

        return $user->subscription();
    }

    /**
     * Cancel the subscription at period end.
     */
    public function cancel(User $user): void
    {
        $user->subscription()->cancel();

        $user->notify(new PlanCancelledNotification);
    }

    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function resume(User $user): Subscription
    {
        $user->subscription()->resume();

        $user->notify(new PlanResumedNotification);

        return $user->subscription();
    }

    /**
     * Assign a plan without Stripe billing, cancelling any active subscription.
     */
    public function assignComplimentary(User $user, Plan $plan): void
    {
        $subscription = $user->subscription();

        if ($subscription && !$subscription->ended()) {
            $subscription->cancelNow();
        }

        $user->complimentary_plan_id = $plan->id;
        $user->fillPlan()->save();

        $user->notify(new PlanChangedNotification($plan));
    }

    /**
     * Announce the subscription once and only once. Stripe repeats
     * customer.subscription.updated for the life of a subscription, and the
     * client syncs on top of that, so the stamp is claimed with a conditional
     * write and the database decides which caller sends the mail.
     */
    protected function announceActivation(User $user, Subscription $subscription): void
    {
        if (!$subscription->valid()) {
            return;
        }

        $plan = Plan::forPriceId($subscription->stripe_price);

        if (!$plan) {
            return;
        }

        $stamped = Subscription::whereKey($subscription->id)
            ->whereNull('activated_at')
            ->update(['activated_at' => now()]);

        if (!$stamped) {
            return;
        }

        $user->notify(new PlanSubscribedNotification($plan));
    }

    /**
     * Read back whichever intent Stripe attached to the subscription. A trial
     * bills nothing up front, so it carries a setup intent to put the card on
     * file, and everything else carries the first invoice's confirmation
     * secret. A fully discounted first invoice needs neither, in which case
     * there is nothing to confirm and the client goes straight to sync.
     */
    protected function intent(Subscription $subscription): array
    {
        $stripeSubscription = $subscription->asStripeSubscription([
            'pending_setup_intent',
            'latest_invoice.confirmation_secret',
        ]);

        if ($stripeSubscription->pending_setup_intent) {
            return [
                'intent_type' => SubscriptionIntent::Setup->value,
                'client_secret' => $stripeSubscription->pending_setup_intent->client_secret,
            ];
        }

        $confirmationSecret = $stripeSubscription->latest_invoice?->confirmation_secret;

        if (!$confirmationSecret) {
            return [
                'intent_type' => SubscriptionIntent::None->value,
                'client_secret' => null,
            ];
        }

        return [
            'intent_type' => SubscriptionIntent::Payment->value,
            'client_secret' => $confirmationSecret->client_secret,
        ];
    }
}
