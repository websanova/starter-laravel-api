<?php

uses()->group('admin.user-restore.update');

use App\Enums\UserRole;
use App\Models\User;

test('admin can restore a soft-deleted user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->delete();

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$target->id}/restore");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.deleted_at', null);

    expect($target->fresh()->deleted_at)->toBeNull();
});

test('super can restore a soft-deleted admin', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);
    $admin->delete();

    $response = $this->actingAs($super)->patchJson("/admin/users/{$admin->id}/restore");

    $response->assertStatus(200);
    expect($admin->fresh()->deleted_at)->toBeNull();
});

test('admin cannot restore a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);
    $super->delete();

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$super->id}/restore");

    $response->assertStatus(403);
});

test('regular user cannot restore a user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $target->delete();

    $response = $this->actingAs($user)->patchJson("/admin/users/{$target->id}/restore");

    $response->assertStatus(403);
});

test('unauthenticated user cannot restore a user', function () {
    $target = User::factory()->create();
    $target->delete();

    $response = $this->patchJson("/admin/users/{$target->id}/restore");

    $response->assertStatus(401);
});
