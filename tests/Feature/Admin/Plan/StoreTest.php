<?php

uses()->group('admin.plan.store');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('super can create a plan', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($super)->postJson('/admin/plans', [
        'name' => 'Enterprise',
        'slug' => 'enterprise',
        'features' => ['bookmarks' => null, 'categories' => null],
        'is_active' => true,
        'tier' => 3,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Enterprise')
        ->assertJsonPath('data.slug', 'enterprise')
        ->assertJsonPath('data.features.bookmarks', null)
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('plans', ['slug' => 'enterprise']);
});

test('admin can create a plan', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(201);
});

test('regular user cannot create a plan', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot create a plan', function () {
    $response = $this->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(401);
});

test('name is required', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'slug' => 'basic',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('slug is required', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'name' => 'Basic',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('slug');
});

test('slug must be unique', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->create(['slug' => 'basic']);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('slug');
});
