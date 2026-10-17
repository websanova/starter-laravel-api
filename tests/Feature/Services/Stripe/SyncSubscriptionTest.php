<?php

uses()->group('service.stripe.sync-subscription');

use App\Models\User;
use App\Services\Stripe\SyncSubscriptionService;
use Laravel\Cashier\Cashier;
use Stripe\PaymentMethod as StripePaymentMethod;

afterEach(fn () => stripeSandboxFlush());

/**
 * A trialing subscription with a local row and no card of its own, which is the
 * state a trial leaves it in. Returns the Stripe subscription id.
 */
function trialingSandboxSubscription(User $user): string
{
    $price = Cashier::stripe()->prices->all(['lookup_keys' => ['pro_monthly'], 'limit' => 1])->data[0];

    $stripeSubscription = Cashier::stripe()->subscriptions->create([
        'customer' => $user->stripe_id,
        'items' => [['price' => $price->id]],
        'trial_period_days' => 7,
    ]);

    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => $stripeSubscription->id,
        'stripe_status' => $stripeSubscription->status,
        'stripe_price' => $price->id,
        'quantity' => 1,
    ]);

    return $stripeSubscription->id;
}

/**
 * The trial step of the sync on its own. A completed checkout session cannot
 * be produced without a browser to confirm it, so the step is reached directly.
 */
function syncTrialPaymentMethod(User $user, ?StripePaymentMethod $paymentMethod): void
{
    (new ReflectionMethod(SyncSubscriptionService::class, 'commitTrialPaymentMethod'))
        ->invoke(app(SyncSubscriptionService::class), $user, $paymentMethod);
}

test('a trialing subscription with no card of its own is pointed at the newest card on the customer', function () {
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user);

    $subscriptionId = trialingSandboxSubscription($user);

    syncTrialPaymentMethod($user, null);

    $user->refresh();

    expect(Cashier::stripe()->subscriptions->retrieve($subscriptionId)->default_payment_method)->toBe($card->id)
        ->and($user->asStripeCustomer()->invoice_settings->default_payment_method)->toBe($card->id)
        ->and($user->pm_type)->toBe('visa')
        ->and($user->pm_last_four)->toBe('4242');
})->group('stripe');

test('a trialing subscription uses the card it carries over a newer one on the customer', function () {
    $user = stripeSandboxUser();

    $carried = stripeSandboxCard($user);

    stripeSandboxCard($user, 'pm_card_mastercard');

    $subscriptionId = trialingSandboxSubscription($user);

    syncTrialPaymentMethod($user, $carried);

    $user->refresh();

    expect(Cashier::stripe()->subscriptions->retrieve($subscriptionId)->default_payment_method)->toBe($carried->id)
        ->and($user->asStripeCustomer()->invoice_settings->default_payment_method)->toBe($carried->id)
        ->and($user->pm_type)->toBe('visa');
})->group('stripe');
