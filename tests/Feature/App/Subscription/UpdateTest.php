<?php

uses()->group('app.subscription.update');

use App\Models\Plan;
use App\Models\User;
use Database\Factories\SubscriptionFactory;

test('unauthenticated user cannot update subscription', function () {
    $response = $this->putJson('/subscription', [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('plan must be public with stripe prices', function () {
    $plan = Plan::factory()->private()->create();
    $user = User::factory()->create();

    SubscriptionFactory::new()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson('/subscription', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});


