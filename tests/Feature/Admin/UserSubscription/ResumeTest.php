<?php

uses()->group('admin.user-subscription.resume');

use App\Enums\UserRole;
use App\Models\User;

test('regular user cannot resume a user subscription', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/admin/users/{$target->id}/subscription/resume");

    $response->assertStatus(403);
});

test('unauthenticated user cannot resume a user subscription', function () {
    $target = User::factory()->create();

    $response = $this->patchJson("/admin/users/{$target->id}/subscription/resume");

    $response->assertStatus(401);
});

test('admin cannot resume a super user subscription', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$super->id}/subscription/resume");

    $response->assertStatus(403);
});
