<?php

namespace App\Listeners\Stripe;

use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class CommitSubscriptionPaymentMethod
{
    /**
     * Promote the card that pays the subscription to the customer default.
     *
     * Nothing this API creates needs it. The card is stored and made the
     * customer default before the subscription exists, and the subscription is
     * created without a default of its own so Stripe falls back to the
     * customer's. What is left is a subscription started from the Stripe
     * dashboard, which arrives carrying a card that never reached the customer.
     * Cashier reads the card columns off the customer default, so without this
     * those users keep a null brand and last four.
     *
     * Cashier already handles the customer side, customer.updated for a card
     * changed through the portal and payment_method.automatically_updated for a
     * network reissue, and both land back here as no ops once the default
     * matches.
     */
    public function handle(WebhookHandled $event): void
    {
        $events = [
            'customer.subscription.created',
            'customer.subscription.updated',
        ];

        if (!in_array($event->payload['type'], $events)) {
            return;
        }

        $data = $event->payload['data']['object'];

        /**
         * Stripe sends an update for renewals, status flips and plan moves as
         * well as for card changes, and previous_attributes carries only the
         * fields that actually changed. Gating on it keeps the provider call
         * below off every unrelated update. A create has no previous state, so
         * it stands on whether the subscription arrived with a card at all,
         * which is the subscription started from the dashboard.
         */
        $previous = $event->payload['data']['previous_attributes'] ?? [];

        if ($event->payload['type'] === 'customer.subscription.updated' && !array_key_exists('default_payment_method', $previous)) {
            return;
        }

        if (!$data['default_payment_method']) {
            return;
        }

        $user = Cashier::findBillable($data['customer']);

        if (!$user) {
            return;
        }

        $user->updateDefaultPaymentMethod($data['default_payment_method']);
    }
}
