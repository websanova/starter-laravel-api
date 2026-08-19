<?php

uses()->group('app.payment-method-intent.store');

use App\Models\User;

test('unauthenticated user cannot open a payment method intent', function () {
    $response = $this->postJson('/payment-method/intent');

    $response->assertStatus(401);
});

test('a user without a stripe customer is turned away', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/payment-method/intent');

    $response->assertStatus(409)
        ->assertJsonPath('message', __('responses.payment_method.customer_required'));
});
