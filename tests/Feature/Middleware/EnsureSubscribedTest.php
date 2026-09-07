<?php

uses()->group('middleware.subscribed');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('freemium mode allows user with a plan', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create();
    $user = User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(200);
});

test('freemium mode allows user without a plan', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $user = User::factory()->create(['plan_id' => null]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(200);
});

test('required mode blocks user without subscription', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Required]);

    $user = User::factory()->create(['plan_id' => null]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(403);
});

test('trial mode blocks user without trial or subscription', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Trial]);

    $user = User::factory()->create(['plan_id' => null, 'trial_ends_at' => null]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(403);
});

test('trial mode allows user within trial period', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Trial]);

    $user = User::factory()->create([
        'plan_id' => null,
        'trial_ends_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(200);
});

test('trial mode blocks user with expired trial', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Trial]);

    $user = User::factory()->create([
        'plan_id' => null,
        'trial_ends_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(403);
});

test('subscription middleware does not block profile routes', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Required]);

    $user = User::factory()->create(['plan_id' => null]);

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200);
});

