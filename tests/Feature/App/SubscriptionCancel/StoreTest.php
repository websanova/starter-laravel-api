<?php

uses()->group('app.subscription-cancel.store');

use App\Models\User;

test('unauthenticated user cannot cancel subscription', function () {
    $response = $this->postJson('/subscription/cancel');

    $response->assertStatus(401);
});

test('user without subscription cannot cancel', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/cancel');

    $response->assertStatus(403);
});


