<?php

uses()->group('service.stripe.replace-payment-method');

use App\Services\Stripe\ReplacePaymentMethodService;

afterEach(fn () => stripeSandboxFlush());

test('the new card becomes the default and the old one is detached', function () {
    $user = stripeSandboxUser();

    $old = stripeSandboxCard($user);

    $user->updateDefaultPaymentMethod($old->id);

    $new = stripeSandboxCard($user, 'pm_card_mastercard');

    app(ReplacePaymentMethodService::class)->handle($user, $new->id);

    $user->refresh();

    expect($user->pm_type)->toBe('mastercard')
        ->and($user->pm_last_four)->toBe('4444')
        ->and($user->asStripeCustomer()->invoice_settings->default_payment_method)->toBe($new->id)
        ->and($user->paymentMethods()->pluck('id')->all())->toBe([$new->id]);
})->group('stripe');

test('a canceled subscription is left alone', function () {
    $user = stripeSandboxUser();

    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_canceled_example',
        'stripe_status' => 'canceled',
        'stripe_price' => 'price_example',
        'quantity' => 1,
        'ends_at' => now()->subDay(),
    ]);

    $card = stripeSandboxCard($user, 'pm_card_mastercard');

    app(ReplacePaymentMethodService::class)->handle($user, $card->id);

    expect($user->refresh()->pm_type)->toBe('mastercard');
})->group('stripe');

test('a second run over the same card changes nothing', function () {
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user);

    $service = app(ReplacePaymentMethodService::class);

    $service->handle($user, $card->id);
    $service->handle($user, $card->id);

    $user->refresh();

    expect($user->pm_type)->toBe('visa')
        ->and($user->pm_last_four)->toBe('4242')
        ->and($user->paymentMethods()->pluck('id')->all())->toBe([$card->id]);
})->group('stripe');
