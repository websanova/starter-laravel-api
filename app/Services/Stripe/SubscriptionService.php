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
     * Commit a billing address, pushing it to Stripe before storing it. The
     * provider write goes first because a local address Stripe does not know
     * about is worse than no address at all, it would let a subscribe through
     * that the provider then rejects for having no tax location.
     *
     * The address keys are Stripe's own, so they pass straight through and are
     * only renamed on the way into the users table.
     */
    public function updateBillingAddress(User $user, array $address): ServiceResult
    {
        $address = array_filter($address);

        if ($address == $user->billingAddress()) {
            return ServiceResult::success();
        }

        /**
         * The first invoice is finalized the moment the subscription is
         * created and never recalculates its tax, so an attempt still in
         * flight would keep charging the old jurisdiction. Killing it makes
         * the next intent() build a fresh one rather than hand back a secret
         * for the stale invoice. Only the two fields a tax location resolves
         * from count, since anything else leaves the amount untouched and
         * tearing up a live payment session over a corrected street name
         * costs the user their progress for nothing.
         */
        $movedTaxLocation = config('subscription.automatic_tax') && (
            ($address['country'] ?? null) !== $user->billing_country ||
            ($address['postal_code'] ?? null) !== $user->billing_postal_code
        );

        try {
            $user->updateOrCreateStripeCustomer(['address' => $address]);

            $subscription = $user->subscription();

            if ($movedTaxLocation && $subscription && $subscription->incomplete()) {
                $subscription->cancelNow();
            }
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        $user->update([
            'billing_city' => $address['city'] ?? null,
            'billing_country' => $address['country'] ?? null,
            'billing_line1' => $address['line1'] ?? null,
            'billing_line2' => $address['line2'] ?? null,
            'billing_postal_code' => $address['postal_code'] ?? null,
        ]);

        return ServiceResult::success();
    }

    /**
     * Open a payment session and hand back the secret a payment element mounts
     * against. The subscription is created here and up front, sitting
     * incomplete until the client confirms it, so this has to stay safe to
     * call again on a page refresh or on a second attempt after the user
     * walked away from the first one.
     */
    public function intent(User $user, Plan $plan, PlanInterval $interval): ServiceResult
    {
        /**
         * Tax is calculated from the customer's billing address, and nothing
         * in this flow collects one, so it has to already be on the user.
         * Stripe rejects the create outright without it, and a local failure
         * that names the reason beats a provider error the client cannot act
         * on.
         */
        if (config('subscription.automatic_tax') && !$user->hasBillingAddress()) {
            return ServiceResult::error('address_required');
        }

        $priceId = $plan->priceId($interval);
        $subscription = $user->subscription();

        try {
            /**
             * The local status is only as fresh as the last webhook, and this
             * is hit again the moment the payment page reloads. Pull the real
             * status before deciding anything, or a subscription paid seconds
             * ago still reads incomplete and gets torn down below. It also
             * lets Stripe's own expiry land, which drops through to a fresh
             * attempt untouched.
             */
            if ($subscription && $subscription->incomplete()) {
                $subscription->syncStripeStatus();
            }

            if ($subscription && $subscription->valid()) {
                return ServiceResult::error('already_subscribed');
            }

            /**
             * TODO: Past due and unpaid are billing failures on a subscription
             * that already exists. Stripe keeps that subscription and its open
             * invoice, so the fix is to attach a new payment method and retry
             * the invoice, not to open a second subscription alongside the
             * failing one. Until that endpoint exists the request is rejected
             * here.
             */
            if ($subscription && ($subscription->pastDue() || $subscription->stripe_status === StripeSubscription::STATUS_UNPAID)) {
                return ServiceResult::error('payment_required');
            }

            /**
             * An incomplete subscription is an attempt still in flight. The
             * same price is the refresh case, so it is handed back as is and
             * Stripe keeps the invoice and its element session alive. A
             * different price means they changed their mind on the page, and
             * swap refuses to touch an incomplete subscription, so the old
             * attempt is cancelled outright and a new one built.
             */
            $reuse = $subscription && $subscription->incomplete() && $subscription->stripe_price === $priceId;

            if ($subscription && $subscription->incomplete() && !$reuse) {
                $subscription->cancelNow();
            }

            if (!$reuse) {
                $builder = $user->newSubscription('default', $priceId)->ignoreIncompletePayments();

                if ($trialEndsAt = $user->resolveTrialEnd()) {
                    $builder->trialUntil($trialEndsAt);
                }

                /**
                 * Stripe leaves the subscription's default payment method
                 * unset unless it is told to keep the one that paid, and
                 * sync() reads the card off exactly that field, so renewals
                 * would bill against nothing without this.
                 */
                $options = [
                    'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                ];

                if (config('subscription.automatic_tax')) {
                    $options['automatic_tax'] = ['enabled' => true];
                }

                $subscription = $builder->create(null, [], $options);
            }

            /**
             * A subscription with something to charge for exposes the secret on
             * its invoice, while one starting on a trial has nothing to charge
             * yet, so Stripe hangs a setup intent off it instead. The client
             * confirms those two with different calls, so which one came back
             * goes along with it.
             */
            $stripeSubscription = $subscription->asStripeSubscription([
                'latest_invoice.confirmation_secret',
                'pending_setup_intent',
            ]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        if ($secret = $stripeSubscription->latest_invoice->confirmation_secret->client_secret ?? null) {
            return ServiceResult::success(['client_secret' => $secret, 'type' => 'payment']);
        }

        if ($secret = $stripeSubscription->pending_setup_intent->client_secret ?? null) {
            return ServiceResult::success(['client_secret' => $secret, 'type' => 'setup']);
        }

        return ServiceResult::error('provider_unavailable');
    }

    /**
     * Pull the live status from the provider and commit it locally. Cashier's
     * own webhook handler writes the subscription row before anything here
     * runs, so nothing calls this today. It stays as the entry point for a
     * provider whose webhooks do not commit on their own.
     */
    public function sync(User $user): void
    {
        $subscription = $user->subscription();

        if (!$subscription) {
            return;
        }

        $subscription->syncStripeStatus();
    }

    /**
     * Commit the plan the user is entitled to and announce the move. The write
     * itself is idempotent and only the caller that moves the plan is told so,
     * which is what keeps a repeated webhook delivery quiet. A move to no plan
     * at all announces nothing, since the cancellation was already announced by
     * whatever ended the subscription.
     */
    public function commitPlan(User $user): void
    {
        $hadPlan = (bool) $user->plan_id;
        $planId = $user->resolvePlanId();

        if (!$user->claimPlan($planId) || !$planId) {
            return;
        }

        $plan = Plan::cached()->firstWhere('id', $planId);

        $user->notify($hadPlan
            ? new PlanChangedNotification($plan)
            : new PlanSubscribedNotification($plan));
    }

    /**
     * Commit the card details shown in the account. Stripe stamps the card on
     * the subscription only, and Cashier reads the card columns off the
     * customer default, so without this promotion the user row keeps a null
     * brand and last four after a successful signup.
     */
    public function commitPaymentMethod(User $user): void
    {
        $subscription = $user->subscription();

        if (!$subscription) {
            return;
        }

        $stripeSubscription = $subscription->asStripeSubscription();

        if ($stripeSubscription->default_payment_method) {
            $user->updateDefaultPaymentMethod($stripeSubscription->default_payment_method);
        }
    }

    /**
     * Swap to a different plan.
     */
    public function swap(User $user, Plan $plan, PlanInterval $interval): Subscription
    {
        $user->subscription()->swap($plan->priceId($interval));

        $user->update(['plan_id' => $user->resolvePlanId()]);

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
        $user->update(['plan_id' => $user->resolvePlanId()]);

        $user->notify(new PlanChangedNotification($plan));
    }
}
