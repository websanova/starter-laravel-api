<?php

uses()->group('account.subscription.store');

use App\Models\Plan;
use App\Models\User;

test('unauthenticated user cannot subscribe', function () {
    $response = $this->putJson('/account/subscription', [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('plan slug is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/account/subscription', [
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

test('interval is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/account/subscription', [
        'plan' => 'pro',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('interval');
});

test('plan must exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/account/subscription', [
        'plan' => 'nonexistent',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

test('interval must be valid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/account/subscription', [
        'plan' => 'pro',
        'interval' => 'weekly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('interval');
});

test('complimentary plan is rejected', function () {
    $plan = Plan::factory()->complimentary()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson('/account/subscription', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

