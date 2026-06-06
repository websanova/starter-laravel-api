<?php

uses()->group('account.subscription.destroy');

use App\Models\User;

test('unauthenticated user cannot cancel subscription', function () {
    $response = $this->deleteJson('/account/subscription');

    $response->assertStatus(401);
});

test('user without subscription cannot cancel', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/account/subscription');

    $response->assertStatus(403);
});
