<?php

uses()->group('admin.user.update');

use App\Enums\UserRole;
use App\Models\User;

test('admin can update a regular user name', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$target->id}", [
        'first_name' => 'Updated',
        'last_name' => 'Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.first_name', 'Updated')
        ->assertJsonPath('data.last_name', 'Name');
});

test('admin can partially update a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['last_name' => 'Original']);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$target->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.first_name', 'Updated')
        ->assertJsonPath('data.last_name', 'Original');
});

test('super can update any user', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();

    $response = $this->actingAs($super)->patchJson("/admin/users/{$target->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.first_name', 'Updated');
});

test('super can update an admin user', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($super)->patchJson("/admin/users/{$admin->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.first_name', 'Updated');
});

test('admin cannot update a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$super->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertStatus(403);
});

test('regular user cannot update another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/admin/users/{$target->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot update a user', function () {
    $target = User::factory()->create();

    $response = $this->patchJson("/admin/users/{$target->id}", [
        'first_name' => 'Updated',
    ]);

    $response->assertStatus(401);
});

test('admin can update a user locale and timezone', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$target->id}", [
        'locale' => 'en',
        'timezone' => 'Europe/London',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.locale', 'en')
        ->assertJsonPath('data.timezone', 'Europe/London');
});

test('update ignores email field', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['email' => 'original@example.com']);

    $response = $this->actingAs($admin)->patchJson("/admin/users/{$target->id}", [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.email', 'original@example.com');
});
