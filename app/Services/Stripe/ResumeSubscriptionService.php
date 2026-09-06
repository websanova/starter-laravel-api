<?php

namespace App\Services\Stripe;

use App\Contracts\ResumeSubscriptionProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Laravel\Cashier\Subscription;
use Stripe\Exception\ApiErrorException;

class ResumeSubscriptionService implements ResumeSubscriptionProvider
{
    /**
     * Resume a cancelled subscription before the period ends. The existing
     * Stripe subscription carries on, so the plan and interval are not picked
     * again and nothing is charged.
     */
    public function handle(User $user): ServiceResult
    {
        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        if (!$subscription) {
            return ServiceResult::error('nothing_to_resume');
        }

        /**
         * Not cancelled is not a refusal. The request is satisfied, so the
         * current state goes back and Stripe is left alone. A double submit, a
         * second tab and a direct call all land here. It goes first so an
         * already running subscription is never refused over a card it is not
         * about to be billed on.
         */
        if (!$subscription->canceled()) {
            return ServiceResult::success($subscription);
        }

        try {
            if (!$this->hasRenewalPaymentMethod($user, $subscription)) {
                return ServiceResult::error('payment_method_missing');
            }

            /**
             * Cashier's own resume() sends a trial_end alongside this, which
             * ends a trial the moment a non trialing subscription resumes. The
             * term is not being moved here, only the cancellation cleared, so
             * the renewal stays the one it was always going to be.
             */
            $stripeSubscription = $subscription->updateStripeSubscription([
                'cancel_at_period_end' => false,
            ]);
        } catch (ApiErrorException $e) {
            return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
        }

        $subscription->fill([
            'stripe_status' => $stripeSubscription->status,
            'ends_at' => null,
        ])->save();

        return ServiceResult::success($subscription);
    }

    /**
     * Whether a payment method resolves for the renewal. Stripe accepts the
     * resume either way, since clearing cancel_at_period_end raises no invoice
     * and charges nothing, so the failure lands weeks later at the renewal
     * instead and drops the user into dunning.
     *
     * The subscription default comes first and the customer is the fallback,
     * which is the order the renewal itself reads. Not the pm_type column,
     * which is display and lags the webhook.
     */
    protected function hasRenewalPaymentMethod(User $user, Subscription $subscription): bool
    {
        if ($subscription->asStripeSubscription()->default_payment_method) {
            return true;
        }

        return (bool) $user->defaultPaymentMethod();
    }
}
