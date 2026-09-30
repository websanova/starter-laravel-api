<?php

uses()->group('app.payment-method-intent.store');

use App\Models\User;

afterEach(fn () => stripeSandboxFlush());

test('unauthenticated user cannot open a setup intent', function () {
    $response = $this->postJson('/payment-method/intent');

    $response->assertStatus(401);
});

test('user without a stripe customer cannot open a setup intent', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/payment-method/intent');

    $response->assertStatus(409)
        ->assertJsonPath('error', 'address_required');
});

test('user without an address cannot open a setup intent', function () {
    $user = stripeSandboxUser();

    $response = $this->actingAs($user)->postJson('/payment-method/intent');

    $response->assertStatus(409)
        ->assertJsonPath('error', 'address_required');
})->group('stripe');

test('user with an address can open a setup intent', function () {
    $user = stripeSandboxUser();

    $user->updateStripeCustomer([
        'address' => [
            'city' => 'Toronto',
            'country' => 'CA',
            'line1' => '100 Queen Street West',
            'postal_code' => 'M5H 2N2',
            'state' => 'ON',
        ],
    ]);

    $response = $this->actingAs($user)->postJson('/payment-method/intent');

    $response->assertStatus(200)
        ->assertJsonPath('data.type', 'setup');

    expect($response->json('data.client_secret'))->toStartWith('seti_');
})->group('stripe');
