<?php

uses()->group('admin.user.show');

use App\Enums\UserRole;
use App\Models\User;

test('super can view any user', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'locale', 'timezone', 'email', 'role', 'is_password_reset_required', 'email_verified_at', 'last_active_at', 'created_at', 'updated_at', 'deleted_at'],
        ]);
});

test('admin can view a regular user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}");

    $response->assertStatus(200);
});

test('admin can view another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$otherAdmin->id}");

    $response->assertStatus(200);
});

test('admin cannot view a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$super->id}");

    $response->assertStatus(403);
});

test('regular user cannot view another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/users/{$target->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot view a user', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/admin/users/{$user->id}");

    $response->assertStatus(401);
});
