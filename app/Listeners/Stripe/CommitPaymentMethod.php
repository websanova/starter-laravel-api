<?php

namespace App\Listeners\Stripe;

use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Events\WebhookReceived;

class CommitPaymentMethod
{
    /**
     * Cashier handles the subscription events itself, so these arrive already
     * committed locally and only the card needs picking up.
     */
    public function handleWebhookHandled(WebhookHandled $event): void
    {
        $events = [
            'customer.subscription.created',
            'customer.subscription.updated',
        ];

        if (in_array($event->payload['type'], $events)) {
            $this->commitSubscriptionPaymentMethod($event->payload);
        }
    }

    /**
     * Cashier has no handler for setup intents and only dispatches
     * WebhookHandled for the types it handles itself, so this side has to hook
     * the earlier event to see them at all.
     */
    public function handleWebhookReceived(WebhookReceived $event): void
    {
        if ($event->payload['type'] === 'setup_intent.succeeded') {
            $this->commitSetupPaymentMethod($event->payload);
        }
    }

    /**
     * Promote the card that pays the subscription to the customer default.
     * Stripe is told to save the payment method on the subscription, so it
     * never reaches the customer on its own, and Cashier reads the card columns
     * off the customer default. Without this the user row keeps a null brand
     * and last four after a successful signup.
     *
     * Cashier already handles the customer side, customer.updated for a card
     * changed through the portal and payment_method.automatically_updated for a
     * network reissue, and both land back here as no ops once the default
     * matches.
     *
     * The subscription is where the card came from, so its own default is
     * already correct and only the customer side is missing.
     */
    protected function commitSubscriptionPaymentMethod(array $payload): void
    {
        $data = $payload['data']['object'];

        /**
         * Stripe sends an update for renewals, status flips and plan moves as
         * well as for card changes, and previous_attributes carries only the
         * fields that actually changed. Gating on it keeps the provider call
         * below off every unrelated update. A create has no previous state, so
         * it stands on whether the subscription arrived with a card at all,
         * which is the subscription started from the dashboard.
         */
        $previous = $payload['data']['previous_attributes'] ?? [];

        if ($payload['type'] === 'customer.subscription.updated' && !array_key_exists('default_payment_method', $previous)) {
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

    /**
     * Commit a card the user entered on its own, outside any subscription flow.
     * Confirming the setup intent only attaches the card to the customer, it
     * sets no default anywhere, so nothing bills against it until this runs.
     *
     * The subscription write is the one that moves the next renewal onto the
     * new card, since Stripe bills a subscription off its own default and only
     * falls back to the customer when it has none.
     */
    protected function commitSetupPaymentMethod(array $payload): void
    {
        $data = $payload['data']['object'];

        if (!$data['payment_method']) {
            return;
        }

        $user = Cashier::findBillable($data['customer']);

        if (!$user) {
            return;
        }

        $user->loadMissing('subscriptions');

        $subscription = $user->subscription();

        if ($subscription) {
            $subscription->updateStripeSubscription(['default_payment_method' => $data['payment_method']]);
        }

        $user->updateDefaultPaymentMethod($data['payment_method']);
    }
}
