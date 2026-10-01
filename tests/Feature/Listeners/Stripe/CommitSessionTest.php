<?php

uses()->group('listener.stripe.commit-session');

use App\Models\User;
use Laravel\Cashier\Events\WebhookReceived;

test('a payload for another event type is ignored', function () {
    event(new WebhookReceived(['type' => 'invoice.paid']));
})->throwsNoExceptions();

test('a session that is not subscription mode is ignored', function () {
    $user = User::factory()->create();

    $user->forceFill(['stripe_id' => 'cus_example'])->save();

    event(new WebhookReceived([
        'type' => 'checkout.session.completed',
        'data' => ['object' => ['id' => 'cs_example', 'customer' => 'cus_example', 'mode' => 'setup']],
    ]));

    expect($user->subscriptions()->count())->toBe(0);
});

test('a session for an unknown customer is ignored', function () {
    event(new WebhookReceived([
        'type' => 'checkout.session.completed',
        'data' => ['object' => ['id' => 'cs_example', 'customer' => 'cus_unknown', 'mode' => 'subscription']],
    ]));
})->throwsNoExceptions();
