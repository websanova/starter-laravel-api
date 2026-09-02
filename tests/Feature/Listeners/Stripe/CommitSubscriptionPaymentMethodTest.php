<?php

uses()->group('listener.stripe.commit-subscription-payment-method');

use App\Models\User;
use Laravel\Cashier\Events\WebhookHandled;

afterEach(fn () => stripeSandboxFlush());

test('a payload for another event type is ignored', function () {
    event(new WebhookHandled(['type' => 'invoice.paid']));
})->throwsNoExceptions();

test('an update that did not change the payment method is ignored', function () {
    $user = User::factory()->create();

    $user->forceFill(['stripe_id' => 'cus_example'])->save();

    event(new WebhookHandled([
        'type' => 'customer.subscription.updated',
        'data' => [
            'object' => [
                'customer' => 'cus_example',
                'default_payment_method' => 'pm_example',
                'status' => 'active',
                'items' => ['data' => [['price' => ['id' => 'price_example']]]],
            ],
            'previous_attributes' => ['status' => 'past_due'],
        ],
    ]));

    expect($user->refresh()->pm_type)->toBeNull();
});

test('a subscription carrying no payment method is ignored', function () {
    $user = User::factory()->create();

    $user->forceFill(['stripe_id' => 'cus_example'])->save();

    event(new WebhookHandled([
        'type' => 'customer.subscription.created',
        'data' => [
            'object' => [
                'customer' => 'cus_example',
                'default_payment_method' => null,
                'status' => 'active',
                'items' => ['data' => [['price' => ['id' => 'price_example']]]],
            ],
        ],
    ]));

    expect($user->refresh()->pm_type)->toBeNull();
});

test('a subscription for an unknown customer is ignored', function () {
    event(new WebhookHandled([
        'type' => 'customer.subscription.created',
        'data' => [
            'object' => [
                'customer' => 'cus_unknown',
                'default_payment_method' => 'pm_example',
                'status' => 'active',
                'items' => ['data' => [['price' => ['id' => 'price_example']]]],
            ],
        ],
    ]));
})->throwsNoExceptions();

test('a subscription created with a card promotes it to the customer default', function () {
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user, 'pm_card_mastercard');

    event(new WebhookHandled([
        'type' => 'customer.subscription.created',
        'data' => [
            'object' => [
                'customer' => $user->stripe_id,
                'default_payment_method' => $card->id,
                'status' => 'active',
                'items' => ['data' => [['price' => ['id' => 'price_example']]]],
            ],
        ],
    ]));

    $user->refresh();

    expect($user->pm_type)->toBe('mastercard')
        ->and($user->asStripeCustomer()->invoice_settings->default_payment_method)->toBe($card->id);
})->group('stripe');
