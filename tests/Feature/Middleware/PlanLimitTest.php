<?php

uses()->group('middleware.plan-limit');

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Tag;
use App\Models\User;

test('user within bookmark limit can create a bookmark', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create(['features' => ['bookmarks' => 5]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(201);
});

test('user at bookmark limit cannot create a bookmark', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create(['features' => ['bookmarks' => 2]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    Bookmark::factory()->count(2)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(403);
});

test('user with unlimited bookmarks can always create', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create(['features' => ['bookmarks' => null]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    Bookmark::factory()->count(100)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(201);
});

test('user at category limit cannot create a category', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create(['features' => ['categories' => 1]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    Category::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/categories', [
        'name' => 'New Category',
    ]);

    $response->assertStatus(403);
});

test('user at tag limit cannot create a tag', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create(['features' => ['tags' => 1]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    Tag::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/tags', [
        'name' => 'newtag',
    ]);

    $response->assertStatus(403);
});

test('user without a plan feature defined gets no limit', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $plan = Plan::factory()->create(['features' => []]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(201);
});

test('user without a plan gets free plan limits', function () {
    config(['subscription.mode' => \App\Enums\SubscriptionMode::Freemium]);

    $freePlan = Plan::where('slug', 'free')->first();
    $freePlan->update(['features' => ['bookmarks' => 1]]);

    $user = User::factory()->create(['plan_id' => null]);

    Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(403);
});

