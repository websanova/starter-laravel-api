<?php

namespace App\Listeners\Stripe;

use App\Contracts\SyncSubscriptionProvider;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\Checkout\Session;

class CommitSession
{
    public function __construct(private SyncSubscriptionProvider $subscriptions)
    {
    }

    /**
     * Commit a subscription the user paid for but never reported back. The
     * client calls the sync itself on the happy path, so this is the backstop
     * for the ones where it cannot. A browser that died after the confirm, a
     * bank challenge that cleared in a tab the user had already closed, or a
     * sync call that errored on work Stripe had already settled.
     *
     * Hooks WebhookReceived rather than WebhookHandled because Cashier has no
     * handler for checkout sessions and only dispatches WebhookHandled for the
     * types it handles itself.
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->payload['type'] !== 'checkout.session.completed') {
            return;
        }

        $data = $event->payload['data']['object'];

        if ($data['mode'] !== Session::MODE_SUBSCRIPTION) {
            return;
        }

        $user = Cashier::findBillable($data['customer']);

        if (!$user) {
            return;
        }

        $this->subscriptions->handle($user, $data['id']);
    }
}
