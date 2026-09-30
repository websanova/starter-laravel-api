<?php

uses()->group('service.stripe.create-session');

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use App\Services\Stripe\CreateSessionService;
use Laravel\Cashier\Cashier;

afterEach(fn () => stripeSandboxFlush());

/**
 * A plan whose monthly price is a real one in the sandbox, since the session is
 * created against the price id held locally.
 */
function createSessionPlan(): Plan
{
    $price = Cashier::stripe()->prices->all(['lookup_keys' => ['pro_monthly'], 'limit' => 1])->data[0];

    $plan = Plan::factory()->create();

    Price::factory()->for($plan)->create([
        'interval' => PlanInterval::Monthly,
        'stripe_price_id' => $price->id,
    ]);

    return $plan;
}

test('every supported user locale has a stripe locale mapping', function () {
    $map = (new ReflectionClass(CreateSessionService::class))->getConstant('LOCALES');

    foreach (config('user.supported_locales') as $locale) {
        expect($map)->toHaveKey($locale);
    }
});

test('opening a session expires the one before it', function () {
    $user = stripeSandboxUser();

    $plan = createSessionPlan();

    $service = app(CreateSessionService::class);

    $first = $service->handle($user, $plan, PlanInterval::Monthly);

    $service->handle($user, $plan, PlanInterval::Monthly);

    $session = Cashier::stripe()->checkout->sessions->retrieve($first->data['id']);

    expect($session->status)->toBe('expired');
})->group('stripe');

test('user with no card on file is asked for an address', function () {
    $user = stripeSandboxUser();

    $result = app(CreateSessionService::class)->handle($user, createSessionPlan(), PlanInterval::Monthly);

    $session = Cashier::stripe()->checkout->sessions->retrieve($result->data['id']);

    expect($session->billing_address_collection)->toBe('required');
})->group('stripe');

test('user with a card on file is not asked for an address', function () {
    $user = stripeSandboxUser();

    stripeSandboxCard($user);

    /**
     * The address goes on with the card because that is the state the branch is
     * written for, the payment method flow having written both. Automatic tax
     * refuses a create that neither carries an address nor collects one.
     */
    $user->updateStripeCustomer([
        'name' => 'Ada Lovelace',
        'address' => [
            'city' => 'Toronto',
            'country' => 'CA',
            'line1' => '100 Queen Street West',
            'postal_code' => 'M5H 2N2',
            'state' => 'ON',
        ],
    ]);

    $result = app(CreateSessionService::class)->handle($user, createSessionPlan(), PlanInterval::Monthly);

    $session = Cashier::stripe()->checkout->sessions->retrieve($result->data['id']);

    expect($session->billing_address_collection)->toBeNull();
})->group('stripe');
