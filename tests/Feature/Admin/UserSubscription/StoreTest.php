<?php

uses()->group('admin.user-subscription.store');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('regular user cannot subscribe another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->putJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot subscribe a user', function () {
    $target = User::factory()->create();

    $response = $this->putJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('admin cannot subscribe a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->putJson("/admin/users/{$super->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(403);
});

test('plan slug is required', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->putJson("/admin/users/{$target->id}/subscription", [
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});

test('interval is optional', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->putJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'pro',
    ]);

    $response->assertJsonMissingValidationErrors('interval');
});

test('plan must exist', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->putJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'nonexistent',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('plan');
});
