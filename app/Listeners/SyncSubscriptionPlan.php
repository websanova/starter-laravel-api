<?php

namespace App\Listeners;

use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class SyncSubscriptionPlan
{
    /**
     * Recompute the cached plan whenever Stripe moves a subscription.
     *
     * Portal swaps, dunning and payments that fail their way to cancelled
     * never touch our own write paths, so the webhook is the only signal
     * that the entitlement behind the plan_id column has changed.
     */
    public function handle(WebhookHandled $event): void
    {
        $events = [
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ];

        if (!in_array($event->payload['type'], $events)) {
            return;
        }

        Cashier::findBillable($event->payload['data']['object']['customer'])
            ?->fillPlan()
            ->save();
    }
}
