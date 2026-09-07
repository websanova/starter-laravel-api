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
        ->assertJsonPath('error', 'customer_missing');
});

test('user with a stripe customer can open a setup intent', function () {
    $user = stripeSandboxUser();

    $response = $this->actingAs($user)->postJson('/payment-method/intent');

    $response->assertStatus(200)
        ->assertJsonPath('data.type', 'setup');

    expect($response->json('data.client_secret'))->toStartWith('seti_');
})->group('stripe');
