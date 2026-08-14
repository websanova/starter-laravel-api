<?php

namespace App\Listeners\Stripe;

use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;

class CommitSetupPaymentMethod
{
    /**
     * Commit a card the user entered on its own, outside any subscription flow.
     * Confirming the setup intent only attaches the card to the customer, it
     * sets no default anywhere, so nothing bills against it until this runs.
     *
     * Hooks WebhookReceived rather than WebhookHandled because Cashier has no
     * handler for setup intents and only dispatches WebhookHandled for the types
     * it handles itself.
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->payload['type'] !== 'setup_intent.succeeded') {
            return;
        }

        $data = $event->payload['data']['object'];

        if (!$data['payment_method']) {
            return;
        }

        $user = Cashier::findBillable($data['customer']);

        if (!$user) {
            return;
        }

        /**
         * Stripe bills a subscription off its own default and only falls back
         * to the customer when it has none, so this is the write that actually
         * moves the next renewal onto the new card.
         */
        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        if ($subscription) {
            $subscription->updateStripeSubscription(['default_payment_method' => $data['payment_method']]);
        }

        $user->updateDefaultPaymentMethod($data['payment_method']);
    }
}
