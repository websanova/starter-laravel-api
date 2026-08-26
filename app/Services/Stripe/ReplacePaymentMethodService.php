<?php

namespace App\Services\Stripe;

use App\Models\User;

class ReplacePaymentMethodService
{
    /**
     * Put a card Stripe has already attached in place as the one on file.
     * Confirming a setup intent only attaches, it sets no default anywhere, so
     * until this runs the card sits on the customer and nothing bills it.
     *
     * Shared by the webhook and the sync, which differ only in how they come by
     * the card, not in what has to happen to it. Both fire on every update and
     * can overlap, so nothing here is guarded. Every write sets the same value
     * and Cashier re-reads the provider before each one, which leaves a second
     * pass inert.
     *
     * The exception is two detaches colliding inside that re-read window, where
     * Stripe throws on the second. The webhook retries into a clean no op, the
     * sync hands the client a provider error on work that actually landed.
     */
    public function handle(User $user, string $paymentMethodId): void
    {
        /**
         * Stripe bills a subscription off its own default and only falls back
         * to the customer when it has none. Nothing this API creates sets one,
         * so this is here for a subscription started from the Stripe dashboard
         * that arrived carrying a card, where leaving it pointed at the old one
         * would bill it on the next renewal. It goes first because a failure
         * part way through is better left billing the new card against a stale
         * display than the reverse.
         */
        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        if ($subscription) {
            $subscription->updateStripeSubscription(['default_payment_method' => $paymentMethodId]);
        }

        $user->updateDefaultPaymentMethod($paymentMethodId);

        /**
         * Sweeping every other card off the customer rather than detaching one
         * remembered id. A run that died before this point left its old card
         * attached with nothing pointing at it, and that id cannot be looked up
         * afterwards, so the next update clears it here instead.
         *
         * This goes last because Cashier wipes the card columns when the card
         * it detaches is still the customer default, which is exactly what the
         * old card is until the line above moves it.
         */
        foreach ($user->paymentMethods() as $paymentMethod) {
            if ($paymentMethod->id !== $paymentMethodId) {
                $user->deletePaymentMethod($paymentMethod->id);
            }
        }
    }
}
