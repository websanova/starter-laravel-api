<?php

uses()->group('admin.user.destroy');

use App\Enums\Role;
use App\Models\User;

test('admin can soft delete a regular user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}");

    $response->assertStatus(204);
    $this->assertSoftDeleted('users', ['id' => $target->id]);
});

test('user tokens are revoked on soft delete', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $target = User::factory()->create();
    $target->createToken('auth');

    $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}");

    expect($target->tokens()->count())->toBe(0);
});

test('super can soft delete any user', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$admin->id}");

    $response->assertStatus(204);
    $this->assertSoftDeleted('users', ['id' => $admin->id]);
});

test('admin cannot soft delete another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(Role::Admin);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$otherAdmin->id}");

    $response->assertStatus(403);
});

test('admin cannot soft delete a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$super->id}");

    $response->assertStatus(403);
});

test('regular user cannot soft delete another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot soft delete a user', function () {
    $target = User::factory()->create();

    $response = $this->deleteJson("/admin/users/{$target->id}");

    $response->assertStatus(401);
});
