<?php

uses()->group('admin.user-role.update');

use App\Enums\Role;
use App\Models\User;

test('admin can assign admin role to a regular user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$target->id}/role", [
        'role' => 'admin',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', 'admin');

    expect($target->fresh()->hasRole(Role::Admin))->toBeTrue();
});

test('super can assign admin role to a regular user', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    $target = User::factory()->create();

    $response = $this->actingAs($super)->patchJson("/admin/users/{$target->id}/role", [
        'role' => 'admin',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', 'admin');
});

test('super can assign super role to a user', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    $target = User::factory()->create();

    $response = $this->actingAs($super)->patchJson("/admin/users/{$target->id}/role", [
        'role' => 'super',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', 'super');
});

test('super can remove admin role from an admin', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    $target = User::factory()->create();
    $target->assignRole(Role::Admin);

    $response = $this->actingAs($super)->patchJson("/admin/users/{$target->id}/role", [
        'role' => null,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', null);

    expect($target->fresh()->hasRole(Role::Admin))->toBeFalse();
});

test('admin cannot remove admin role from another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(Role::Admin);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$otherAdmin->id}/role", [
        'role' => null,
    ]);

    $response->assertStatus(403);
});

test('admin cannot assign role to a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$super->id}/role", [
        'role' => 'admin',
    ]);

    $response->assertStatus(403);
});

test('admin cannot assign role to another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(Role::Admin);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$otherAdmin->id}/role", [
        'role' => 'admin',
    ]);

    $response->assertStatus(403);
});

test('regular user cannot assign roles', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/admin/users/{$target->id}/role", [
        'role' => 'admin',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot assign roles', function () {
    $target = User::factory()->create();

    $response = $this->patchJson("/admin/users/{$target->id}/role", [
        'role' => 'admin',
    ]);

    $response->assertStatus(401);
});

test('invalid role value is rejected', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$target->id}/role", [
        'role' => 'invalid',
    ]);

    $response->assertStatus(422);
});
