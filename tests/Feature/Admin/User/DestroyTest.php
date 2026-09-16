<?php

uses()->group('admin.user.destroy');

use App\Enums\UserRole;
use App\Models\User;

test('admin can soft delete a regular user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}", ['email' => $target->email]);

    $response->assertStatus(204);
    $this->assertSoftDeleted('users', ['id' => $target->id]);
});

test('user tokens are revoked on soft delete', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->createToken('auth');

    $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}", ['email' => $target->email]);

    expect($target->tokens()->count())->toBe(0);
});

test('super can soft delete any user', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$admin->id}", ['email' => $admin->email]);

    $response->assertStatus(204);
    $this->assertSoftDeleted('users', ['id' => $admin->id]);
});

test('email is required to soft delete a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}");

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
    $this->assertNotSoftDeleted('users', ['id' => $target->id]);
});

test('email must match the user to soft delete', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['email' => 'target@example.com']);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}", ['email' => 'wrong@example.com']);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
    $this->assertNotSoftDeleted('users', ['id' => $target->id]);
});

test('admin cannot soft delete another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$otherAdmin->id}", ['email' => $otherAdmin->email]);

    $response->assertStatus(403);
});

test('admin cannot soft delete a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$super->id}", ['email' => $super->email]);

    $response->assertStatus(403);
});

test('regular user cannot soft delete another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}", ['email' => $target->email]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot soft delete a user', function () {
    $target = User::factory()->create();

    $response = $this->deleteJson("/admin/users/{$target->id}");

    $response->assertStatus(401);
});
