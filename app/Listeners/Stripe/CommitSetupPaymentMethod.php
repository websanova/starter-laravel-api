<?php

namespace App\Listeners\Stripe;

use App\Services\Stripe\ReplacePaymentMethodService;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;

class CommitSetupPaymentMethod
{
    public function __construct(private ReplacePaymentMethodService $paymentMethods)
    {
    }

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

        $this->paymentMethods->handle($user, $data['payment_method']);
    }
}
