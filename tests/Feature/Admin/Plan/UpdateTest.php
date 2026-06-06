<?php

uses()->group('admin.plan.update');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('super can update a plan', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $plan = Plan::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($super)->patchJson("/admin/plans/{$plan->id}", [
        'name' => 'New Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'New Name');
});

test('admin can update a plan', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create();

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}", [
        'name' => 'Updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated');
});

test('regular user cannot update a plan', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $response = $this->actingAs($user)->patchJson("/admin/plans/{$plan->id}", [
        'name' => 'Updated',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot update a plan', function () {
    $plan = Plan::factory()->create();

    $response = $this->patchJson("/admin/plans/{$plan->id}", [
        'name' => 'Updated',
    ]);

    $response->assertStatus(401);
});

test('can update plan features', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create(['features' => ['bookmarks' => 10]]);

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}", [
        'features' => ['bookmarks' => 25, 'categories' => 5],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.features.bookmarks', 25)
        ->assertJsonPath('data.features.categories', 5);
});

test('can update plan slug to a unique value', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create(['slug' => 'original']);

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}", [
        'slug' => 'updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.slug', 'updated');
});

test('cannot update plan slug to an existing slug', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->create(['slug' => 'taken']);
    $plan = Plan::factory()->create(['slug' => 'original']);

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}", [
        'slug' => 'taken',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('slug');
});

test('can update plan slug to its own current value', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create(['slug' => 'same']);

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}", [
        'slug' => 'same',
    ]);

    $response->assertStatus(200);
});

test('can deactivate a plan', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}", [
        'is_active' => false,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.is_active', false);
});
