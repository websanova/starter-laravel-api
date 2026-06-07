<?php

uses()->group('account.subscription.update');

use App\Models\Plan;
use App\Models\User;

test('unauthenticated user cannot update subscription', function () {
    $response = $this->putJson('/account/subscription', [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('plan must be public with stripe prices', function () {
    $plan = Plan::factory()->private()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/account/subscription', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});
