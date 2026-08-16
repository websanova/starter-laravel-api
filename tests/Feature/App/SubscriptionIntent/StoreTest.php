<?php

uses()->group('app.subscription-intent.store');

use App\Models\Plan;
use App\Models\User;
use Database\Factories\SubscriptionFactory;

test('unauthenticated user cannot open a subscription intent', function () {
    $response = $this->postJson('/subscription/intent', [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('plan must be public with stripe prices', function () {
    $plan = Plan::factory()->private()->paid()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/intent', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

test('interval must be a supported billing interval', function () {
    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/intent', [
        'plan' => $plan->slug,
        'interval' => 'fortnightly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('interval');
});

test('a billing address is required when automatic tax is on', function () {
    config(['subscription.automatic_tax' => true]);

    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/intent', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('message', __('responses.subscription.address_required'));
});

test('a user with an active subscription is turned away', function () {
    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    SubscriptionFactory::new()->create([
        'user_id' => $user->id,
        'stripe_price' => $plan->prices->first()->stripe_price_id,
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/intent', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('message', __('responses.subscription.already_subscribed'));
});

test('a user on a trial is turned away', function () {
    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    SubscriptionFactory::new()->trialing()->create([
        'user_id' => $user->id,
        'stripe_price' => $plan->prices->first()->stripe_price_id,
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/intent', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('message', __('responses.subscription.already_subscribed'));
});

test('a past due subscription is sent to payment rather than a second subscription', function () {
    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    SubscriptionFactory::new()->pastDue()->create([
        'user_id' => $user->id,
        'stripe_price' => $plan->prices->first()->stripe_price_id,
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/intent', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('message', __('responses.subscription.payment_required'));
});

test('an unpaid subscription is sent to payment rather than a second subscription', function () {
    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    SubscriptionFactory::new()->unpaid()->create([
        'user_id' => $user->id,
        'stripe_price' => $plan->prices->first()->stripe_price_id,
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/intent', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('message', __('responses.subscription.payment_required'));
});
