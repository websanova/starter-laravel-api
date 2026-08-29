<?php

uses()->group('listener.stripe.commit-setup-payment-method');

use App\Models\User;
use Laravel\Cashier\Events\WebhookReceived;

afterEach(fn () => stripeSandboxFlush());

test('a payload for another event type is ignored', function () {
    event(new WebhookReceived(['type' => 'invoice.paid']));
})->throwsNoExceptions();

test('a setup intent carrying no payment method is ignored', function () {
    $user = User::factory()->create();

    $user->forceFill(['stripe_id' => 'cus_example'])->save();

    event(new WebhookReceived([
        'type' => 'setup_intent.succeeded',
        'data' => ['object' => ['customer' => 'cus_example', 'payment_method' => null]],
    ]));

    expect($user->refresh()->pm_type)->toBeNull();
});

test('a setup intent for an unknown customer is ignored', function () {
    event(new WebhookReceived([
        'type' => 'setup_intent.succeeded',
        'data' => ['object' => ['customer' => 'cus_unknown', 'payment_method' => 'pm_example']],
    ]));
})->throwsNoExceptions();

test('a succeeded setup intent puts the card in place', function () {
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user, 'pm_card_mastercard');

    event(new WebhookReceived([
        'type' => 'setup_intent.succeeded',
        'data' => ['object' => ['customer' => $user->stripe_id, 'payment_method' => $card->id]],
    ]));

    $user->refresh();

    expect($user->pm_type)->toBe('mastercard')
        ->and($user->asStripeCustomer()->invoice_settings->default_payment_method)->toBe($card->id);
})->group('stripe');
