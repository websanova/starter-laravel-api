<?php

uses()->group('app.payment-method-sync.store');

use App\Models\User;

test('unauthenticated user cannot sync a payment method', function () {
    $response = $this->postJson('/payment-method/sync');

    $response->assertStatus(401);
});

test('a user without a stripe customer has nothing to sync', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/payment-method/sync');

    $response->assertStatus(409)
        ->assertJsonPath('message', __('responses.payment_method.nothing_to_sync'));
});
