<?php

namespace App\Listeners;

use App\Services\SubscriptionService;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class SyncSubscription
{
    public function __construct(
        protected SubscriptionService $subscriptions,
    ) {}

    /**
     * Commit the subscription state whenever Stripe moves it.
     *
     * Portal swaps, dunning and payments that fail their way to cancelled never
     * touch our own write paths, so the webhook is the only signal for those.
     * The client calls the same sync after confirming a payment, since this
     * event can land well after the user is already back on the site.
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

        $user = Cashier::findBillable($event->payload['data']['object']['customer']);

        if (!$user) {
            return;
        }

        $this->subscriptions->sync($user);
    }
}
