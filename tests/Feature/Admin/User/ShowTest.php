<?php

uses()->group('admin.user.show');

use App\Enums\Role;
use App\Models\User;

test('super can view any user', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    $target = User::factory()->create();

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'email', 'role', 'is_password_reset_required', 'email_verified_at', 'last_active_at', 'created_at', 'updated_at', 'deleted_at'],
        ]);
});

test('admin can view a regular user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}");

    $response->assertStatus(200);
});

test('admin can view another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(Role::Admin);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$otherAdmin->id}");

    $response->assertStatus(200);
});

test('admin cannot view a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $super = User::factory()->create();
    $super->assignRole(Role::Super);

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
