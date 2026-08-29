<?php

uses()->group('service.stripe.sync-payment-method');

use App\Services\Stripe\SyncPaymentMethodService;

afterEach(fn () => stripeSandboxFlush());

test('a confirmed setup intent puts the card in place', function () {
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user, 'pm_card_mastercard');

    $setupIntent = stripeSandboxSetupIntent($user, $card);

    $result = app(SyncPaymentMethodService::class)->handle($user, $setupIntent->id);

    expect($result->success)->toBeTrue();

    $user->refresh();

    expect($user->pm_type)->toBe('mastercard')
        ->and($user->pm_last_four)->toBe('4444')
        ->and($user->asStripeCustomer()->invoice_settings->default_payment_method)->toBe($card->id);
})->group('stripe');

test('a setup intent belonging to another customer is refused', function () {
    $user = stripeSandboxUser();
    $other = stripeSandboxUser();

    $setupIntent = stripeSandboxSetupIntent($other, stripeSandboxCard($other));

    $result = app(SyncPaymentMethodService::class)->handle($user, $setupIntent->id);

    expect($result->success)->toBeFalse()
        ->and($result->error)->toBe('nothing_to_sync')
        ->and($user->refresh()->pm_type)->toBeNull();
})->group('stripe');

test('an unconfirmed setup intent is refused', function () {
    $user = stripeSandboxUser();

    $setupIntent = stripeSandboxSetupIntent($user, stripeSandboxCard($user), confirm: false);

    $result = app(SyncPaymentMethodService::class)->handle($user, $setupIntent->id);

    expect($result->success)->toBeFalse()
        ->and($result->error)->toBe('nothing_to_sync')
        ->and($user->refresh()->pm_type)->toBeNull();
})->group('stripe');
