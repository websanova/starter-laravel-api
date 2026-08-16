<?php

uses()->group('app.billing-address.update');

use App\Models\User;

test('unauthenticated user cannot update a billing address', function () {
    $response = $this->putJson('/billing/address', [
        'country' => 'US',
        'postal_code' => '10001',
    ]);

    $response->assertStatus(401);
});

test('country and postal code are required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/billing/address', [
        'line1' => '1 Infinite Loop',
        'city' => 'Cupertino',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['country', 'postal_code']);
});

test('country must be a two letter uppercase code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/billing/address', [
        'country' => 'usa',
        'postal_code' => '10001',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('country');
});

/**
 * The service compares the incoming address against the stored one and returns
 * before touching Stripe when nothing moved, so this is the one success path
 * that runs without the provider.
 */
test('an unchanged address is accepted without reaching the provider', function () {
    $address = [
        'city' => 'New York',
        'country' => 'US',
        'line1' => '350 Fifth Ave',
        'line2' => 'Floor 20',
        'postal_code' => '10001',
    ];

    $user = User::factory()->create([
        'billing_city' => $address['city'],
        'billing_country' => $address['country'],
        'billing_line1' => $address['line1'],
        'billing_line2' => $address['line2'],
        'billing_postal_code' => $address['postal_code'],
    ]);

    $response = $this->actingAs($user)->putJson('/billing/address', $address);

    $response->assertStatus(200)
        ->assertJsonPath('message', __('responses.billing.address_updated'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'billing_postal_code' => '10001',
    ]);
});

test('a changed address is pushed to the provider and stored', function () {
    if (!config('cashier.secret')) {
        $this->markTestSkipped('Stripe is not configured.');
    }

    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/billing/address', [
        'city' => 'Cupertino',
        'country' => 'US',
        'line1' => '1 Infinite Loop',
        'postal_code' => '95014',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'billing_city' => 'Cupertino',
        'billing_country' => 'US',
        'billing_line1' => '1 Infinite Loop',
        'billing_postal_code' => '95014',
    ]);
});
