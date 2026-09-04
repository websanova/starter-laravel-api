<?php

namespace App\Services\Stripe;

use App\Contracts\DeletePaymentMethodProvider;
use App\Models\User;
use App\Support\ServiceResult;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class DeletePaymentMethodService implements DeletePaymentMethodProvider
{
    /**
     * Remove the card on file. Stripe has no delete for a payment method, since
     * past charges and invoices reference it by id, so this detaches instead.
     * A detached payment method can never be attached to a customer again,
     * which is what makes removal here permanent.
     */
    public function handle(User $user): ServiceResult
    {
        $user->loadMissing('subscriptions');

        /**
         * The gate is chargeability, not subscription status. Cancelled with
         * the paid term still running and ended both mean no invoice is coming,
         * and Cashier marks both by stamping ends_at, which is what canceled()
         * reads. Refusing while a renewal is still coming is the other half of
         * the same rule, since a renewal with nothing on file books a failed
         * payment and puts the user into dunning over a card they removed on
         * purpose.
         *
         * Incomplete expired is the checkout whose first invoice never cleared.
         * It never renews and nothing ever stamps ends_at on it, so it is named
         * here rather than left refusing forever.
         */
        $subscription = $user->subscription();

        $chargeable = $subscription
            && !$subscription->canceled()
            && $subscription->stripe_status !== StripeSubscription::STATUS_INCOMPLETE_EXPIRED;

        if ($chargeable) {
            return ServiceResult::error('subscription_active');
        }

        /**
         * The customer default is the card on file. A detach takes it off
         * invoice_settings.default_payment_method with it, so one that Stripe
         * already holds as detached, from a dashboard removal or a retried
         * request, reads back as nothing to detach rather than an error.
         */
        if ($user->hasStripeId()) {
            try {
                $paymentMethod = $user->defaultPaymentMethod();

                if ($paymentMethod) {
                    $user->deletePaymentMethod($paymentMethod->id);
                }
            } catch (ApiErrorException $e) {
                return ServiceResult::error('provider_unavailable', ['debug' => [$e->getMessage()]]);
            }
        }

        /**
         * Cashier clears these itself, but only on the path where it did the
         * detaching. Writing them unconditionally is what makes the already
         * detached and never there paths land the same as a real removal.
         */
        $user->forceFill([
            'pm_type' => null,
            'pm_last_four' => null,
        ])->save();

        return ServiceResult::success();
    }
}
