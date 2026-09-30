<?php

uses()->group('app.subscription-session.store');

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use App\Models\User;
use Database\Factories\SubscriptionFactory;
use Laravel\Cashier\Cashier;

afterEach(fn () => stripeSandboxFlush());

test('unauthenticated user cannot open a checkout session', function () {
    $response = $this->postJson('/subscription/session');

    $response->assertStatus(401);
});

test('user with a subscription cannot open a checkout session', function () {
    $plan = Plan::factory()->paid()->create();

    $user = User::factory()->create();

    SubscriptionFactory::new()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/subscription/session', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'already_subscribed');
});

test('user with an outstanding payment cannot open a checkout session', function () {
    $plan = Plan::factory()->paid()->create();

    $user = User::factory()->create();

    SubscriptionFactory::new()->pastDue()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/subscription/session', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'payment_required');
});

test('user without a subscription can open a checkout session', function () {
    $user = stripeSandboxUser();

    $price = Cashier::stripe()->prices->all(['lookup_keys' => ['pro_monthly'], 'limit' => 1])->data[0];

    $plan = Plan::factory()->create();

    Price::factory()->for($plan)->create([
        'interval' => PlanInterval::Monthly,
        'stripe_price_id' => $price->id,
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/session', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(200);

    expect($response->json('data.client_secret'))->not->toBeEmpty()
        ->and($response->json('data.id'))->toStartWith('cs_');
})->group('stripe');
