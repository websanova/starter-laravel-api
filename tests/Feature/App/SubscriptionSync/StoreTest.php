<?php

uses()->group('app.subscription-sync.store');

use App\Models\User;
use Laravel\Cashier\Cashier;

afterEach(fn () => stripeSandboxFlush());

/**
 * An open checkout session against the customer. Nothing here can produce a
 * completed one, since completing it is a confirm in the browser, so the
 * sync's success path is not reachable from a test.
 */
function subscriptionSyncSession(User $user): string
{
    $price = Cashier::stripe()->prices->all(['lookup_keys' => ['pro_monthly'], 'limit' => 1])->data[0];

    return Cashier::stripe()->checkout->sessions->create([
        'ui_mode' => 'elements',
        'mode' => 'subscription',
        'customer' => $user->stripe_id,
        'line_items' => [['price' => $price->id, 'quantity' => 1]],
        'return_url' => config('app.frontend_url') . '/subscribe',
    ])->id;
}

test('unauthenticated user cannot sync a subscription', function () {
    $response = $this->postJson('/subscription/sync', ['session' => 'cs_example']);

    $response->assertStatus(401);
});

test('user must send a session to sync', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/sync', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('session');
});

test('user without a stripe customer has nothing to sync', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/sync', ['session' => 'cs_example']);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'nothing_to_sync');
});

test('a session belonging to another customer has nothing to sync', function () {
    $user = stripeSandboxUser();
    $other = stripeSandboxUser();

    $response = $this->actingAs($user)->postJson('/subscription/sync', [
        'session' => subscriptionSyncSession($other),
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'nothing_to_sync');

    expect($user->subscriptions()->count())->toBe(0);
})->group('stripe');

test('an open session has nothing to sync', function () {
    $user = stripeSandboxUser();

    $response = $this->actingAs($user)->postJson('/subscription/sync', [
        'session' => subscriptionSyncSession($user),
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'nothing_to_sync');

    expect($user->subscriptions()->count())->toBe(0);
})->group('stripe');
