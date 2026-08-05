<?php

namespace App\Listeners\Stripe;

use App\Contracts\SubscriptionProvider;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class SyncSubscription
{
    public function __construct(
        protected SubscriptionProvider $subscriptions,
    ) {}

    /**
     * Commit what Cashier's own handler leaves untouched whenever Stripe moves
     * a subscription.
     *
     * Cashier writes the subscription row itself before this runs, so nothing
     * here re-reads it. What is left is the user side, and since portal swaps,
     * dunning and payments that fail their way to cancelled never touch our own
     * write paths, the webhook is the only signal for any of it.
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

        /**
         * Loaded once here rather than inside each call below. Cashier's own
         * handler wrote the subscription row moments ago, so this is the one
         * entry point where the relation has to be pulled fresh instead of
         * taken as given.
         */
        $user->load('subscriptions');

        $this->subscriptions->commitPlan($user);
        $this->subscriptions->commitPaymentMethod($user);
    }
}
