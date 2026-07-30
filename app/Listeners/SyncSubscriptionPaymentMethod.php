<?php

namespace App\Listeners;

use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class SyncSubscriptionPaymentMethod
{
    /**
     * Promote the card used for a newly activated subscription to the customer default.
     *
     * Stripe only stamps the card on the subscription. Cashier reads the card
     * columns off the customer default, so without this promotion the user row
     * keeps a null brand and last four after a successful signup.
     */
    public function handle(WebhookHandled $event): void
    {
        if ($event->payload['type'] !== 'customer.subscription.updated') {
            return;
        }

        $subscription = $event->payload['data']['object'];

        if ($subscription['status'] !== 'active' || !($subscription['default_payment_method'] ?? null)) {
            return;
        }

        Cashier::findBillable($subscription['customer'])
            ?->updateDefaultPaymentMethod($subscription['default_payment_method']);
    }
}
