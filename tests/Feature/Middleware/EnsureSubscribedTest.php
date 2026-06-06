<?php

uses()->group('middleware.subscribed');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('freemium mode allows user with a plan', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create();
    $user = User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($user)->getJson('/account/bookmarks');

    $response->assertStatus(200);
});

test('freemium mode allows user without a plan', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $user = User::factory()->create(['plan_id' => null]);

    $response = $this->actingAs($user)->getJson('/account/bookmarks');

    $response->assertStatus(200);
});

test('required mode blocks user without subscription', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Required]);

    $plan = Plan::factory()->create();
    $user = User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($user)->getJson('/account/bookmarks');

    $response->assertStatus(403);
});

test('subscription middleware does not block profile routes', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Required]);

    $user = User::factory()->create(['plan_id' => null]);

    $response = $this->actingAs($user)->getJson('/account/profile');

    $response->assertStatus(200);
});
