<?php

uses()->group('admin.user-auth-token.destroy');

use App\Enums\UserRole;
use App\Models\User;

test('admin can delete a user\'s auth token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $token = $target->createToken('auth')->accessToken;
    $remaining = $target->createToken('auth')->accessToken;

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/auth-tokens/{$token->id}");

    $response->assertStatus(204);
    $this->assertModelMissing($token);
    $this->assertModelExists($remaining);
});

test('super can delete a user\'s auth token', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    $token = $target->createToken('auth')->accessToken;

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$target->id}/auth-tokens/{$token->id}");

    $response->assertStatus(204);
    $this->assertModelMissing($token);
});

test('admin can delete another admin\'s auth token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->assignRole(UserRole::Admin);
    $token = $target->createToken('auth')->accessToken;

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/auth-tokens/{$token->id}");

    $response->assertStatus(204);
    $this->assertModelMissing($token);
});

test('admin cannot delete a super user\'s auth token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);
    $token = $super->createToken('auth')->accessToken;

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$super->id}/auth-tokens/{$token->id}");

    $response->assertStatus(403);
    $this->assertModelExists($token);
});

test('regular user cannot delete a user\'s auth token', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $token = $target->createToken('auth')->accessToken;

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/auth-tokens/{$token->id}");

    $response->assertStatus(403);
    $this->assertModelExists($token);
});

test('unauthenticated user cannot delete a user\'s auth token', function () {
    $target = User::factory()->create();
    $token = $target->createToken('auth')->accessToken;

    $response = $this->deleteJson("/admin/users/{$target->id}/auth-tokens/{$token->id}");

    $response->assertStatus(401);
    $this->assertModelExists($token);
});

test('cannot delete a auth token belonging to a different user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();
    $token = $other->createToken('auth')->accessToken;

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/auth-tokens/{$token->id}");

    $response->assertStatus(404);
    $this->assertModelExists($token);
});
