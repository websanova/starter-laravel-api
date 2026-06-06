<?php

uses()->group('account.subscription.update');

use App\Models\Plan;
use App\Models\User;

test('unauthenticated user cannot update subscription', function () {
    $response = $this->patchJson('/account/subscription', [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('user without subscription cannot swap plans', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/account/subscription', [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(403);
});
