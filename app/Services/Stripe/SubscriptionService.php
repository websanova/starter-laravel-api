<?php

namespace App\Services\Stripe;

use App\Contracts\SubscriptionProvider;
use App\Enums\PlanInterval;
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
    /**
     * Open a checkout session and hand back the secret the embedded component
     * mounts against. Stripe owns the whole payment flow from there, including
     * tax, promotion codes and any card authentication, and it creates the
     * subscription itself once the session completes. Nothing is recorded
     * locally here, the webhook commits the result.
     */
    public function start(User $user, Plan $plan, PlanInterval $interval): ServiceResult
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

        $builder = $user->newSubscription('default', $plan->priceId($interval));

        if ($trialEndsAt = $user->resolveTrialEnd()) {
            $builder->trialUntil($trialEndsAt);
        }

        try {
            $checkout = $builder->checkout([
                'ui_mode' => 'embedded',
                'return_url' => config('app.frontend_url'),
                'allow_promotion_codes' => true,
                'automatic_tax' => ['enabled' => true],
            ]);
        } catch (ApiErrorException) {
            return ServiceResult::error('provider_unavailable');
        }

        return ServiceResult::success([
            'client_secret' => $checkout->client_secret,
        ]);
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
}
