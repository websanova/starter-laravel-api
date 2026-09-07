<?php

uses()->group('app.payment-method-sync.store');

use App\Models\User;

afterEach(fn () => stripeSandboxFlush());

test('unauthenticated user cannot sync a payment method', function () {
    $response = $this->postJson('/payment-method/sync', ['setup_intent' => 'seti_example']);

    $response->assertStatus(401);
});

test('user must send a setup intent to sync', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/payment-method/sync', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('setup_intent');
});

test('user without a stripe customer has nothing to sync', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/payment-method/sync', ['setup_intent' => 'seti_example']);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'nothing_to_sync');
});

test('user syncing a confirmed setup intent gets the new card back', function () {
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user, 'pm_card_mastercard');

    $setupIntent = stripeSandboxSetupIntent($user, $card);

    $response = $this->actingAs($user)->postJson('/payment-method/sync', ['setup_intent' => $setupIntent->id]);

    $response->assertStatus(200);

    expect($user->refresh()->pm_type)->toBe('mastercard')
        ->and($user->pm_last_four)->toBe('4444');
})->group('stripe');
