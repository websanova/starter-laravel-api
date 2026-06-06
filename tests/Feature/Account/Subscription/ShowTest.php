<?php

uses()->group('account.subscription.show');

use App\Models\User;

test('authenticated user can view subscription status', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/account/subscription');

    $response->assertStatus(200)
        ->assertJsonPath('data', null);
});

test('unauthenticated user cannot view subscription status', function () {
    $response = $this->getJson('/account/subscription');

    $response->assertStatus(401);
});
