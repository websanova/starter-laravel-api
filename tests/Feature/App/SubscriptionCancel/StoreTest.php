<?php

uses()->group('app.subscription-cancel.store');

use App\Models\User;
use Database\Factories\SubscriptionFactory;
use Laravel\Cashier\Cashier;

afterEach(fn () => stripeSandboxFlush());

test('unauthenticated user cannot cancel subscription', function () {
    $response = $this->postJson('/subscription/cancel');

    $response->assertStatus(401);
});

test('user without subscription cannot cancel', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/cancel');

    $response->assertStatus(403);
});

test('cancelling runs the subscription to the end of the term', function () {
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user);

    $user->updateDefaultPaymentMethod($card->id);

    $price = Cashier::stripe()->prices->all(['lookup_keys' => ['pro_monthly'], 'limit' => 1])->data[0];

    $stripeSubscription = Cashier::stripe()->subscriptions->create([
        'customer' => $user->stripe_id,
        'items' => [['price' => $price->id]],
        'default_payment_method' => $card->id,
    ]);

    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => $stripeSubscription->id,
        'stripe_status' => $stripeSubscription->status,
        'stripe_price' => $price->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/cancel');

    $response->assertStatus(200)
        ->assertJsonPath('data.stripe_status', 'active')
        ->assertJsonPath('data.on_grace_period', true);

    expect($response->json('data.ends_at'))->not->toBeNull()
        ->and(Cashier::stripe()->subscriptions->retrieve($stripeSubscription->id)->cancel_at_period_end)->toBeTrue();
})->group('stripe');

test('cancelling an already cancelled subscription changes nothing', function () {
    $user = User::factory()->create();

    $subscription = SubscriptionFactory::new()->create([
        'user_id' => $user->id,
        'ends_at' => now()->addDays(10),
    ]);

    $endsAt = $subscription->ends_at;

    $response = $this->actingAs($user)->postJson('/subscription/cancel');

    $response->assertStatus(200)
        ->assertJsonPath('data.on_grace_period', true);

    expect($subscription->refresh()->ends_at->eq($endsAt))->toBeTrue();
});

test('a stripe error is relayed and the subscription is left alone', function () {
    $user = stripeSandboxUser();

    $subscription = SubscriptionFactory::new()->create([
        'user_id' => $user->id,
        'stripe_id' => 'sub_missing_example',
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/cancel');

    $response->assertStatus(409)
        ->assertJsonPath('error', 'provider_unavailable');

    expect($subscription->refresh()->ends_at)->toBeNull();
})->group('stripe');

test('a cancelled subscription counts as subscribed until the end date passes', function () {
    $user = User::factory()->create();

    $subscription = SubscriptionFactory::new()->create([
        'user_id' => $user->id,
        'ends_at' => now()->addDays(10),
    ]);

    expect($user->load('subscriptions')->subscribed())->toBeTrue();

    $subscription->update(['ends_at' => now()->subDay()]);

    expect($user->load('subscriptions')->subscribed())->toBeFalse();
});
