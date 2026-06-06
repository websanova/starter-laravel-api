<?php

uses()->group('admin.user-subscription.update');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('regular user cannot update a user subscription', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    Plan::factory()->create(['slug' => 'pro']);

    $response = $this->actingAs($user)->patchJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot update a user subscription', function () {
    $target = User::factory()->create();

    $response = $this->patchJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('admin cannot update a super user subscription', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    Plan::factory()->create(['slug' => 'pro']);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$super->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(403);
});
