<?php

uses()->group('admin.user-category.index');

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;

test('super can list a user\'s categories', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    Category::factory()->count(3)->create(['user_id' => $target->id]);

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}/categories");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'user_id', 'name', 'created_at', 'updated_at']],
        ]);
});

test('admin can list a user\'s categories', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Category::factory()->count(3)->create(['user_id' => $target->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/categories");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('regular user cannot list a user\'s categories', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/users/{$target->id}/categories");

    $response->assertStatus(403);
});

test('unauthenticated user cannot list a user\'s categories', function () {
    $target = User::factory()->create();

    $response = $this->getJson("/admin/users/{$target->id}/categories");

    $response->assertStatus(401);
});

test('returns only the target user\'s categories', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();

    Category::factory()->count(2)->create(['user_id' => $target->id]);
    Category::factory()->count(3)->create(['user_id' => $other->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/categories");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('categories are sorted by name ascending by default', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Category::factory()->create(['user_id' => $target->id, 'name' => 'Zebra']);
    Category::factory()->create(['user_id' => $target->id, 'name' => 'Apple']);
    Category::factory()->create(['user_id' => $target->id, 'name' => 'Mango']);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/categories");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'Apple')
        ->assertJsonPath('data.1.name', 'Mango')
        ->assertJsonPath('data.2.name', 'Zebra');
});

test('categories can be sorted by name descending', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Category::factory()->create(['user_id' => $target->id, 'name' => 'Zebra']);
    Category::factory()->create(['user_id' => $target->id, 'name' => 'Apple']);
    Category::factory()->create(['user_id' => $target->id, 'name' => 'Mango']);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/categories?sort_dir=desc");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'Zebra')
        ->assertJsonPath('data.1.name', 'Mango')
        ->assertJsonPath('data.2.name', 'Apple');
});

test('invalid sort_by value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/categories?sort_by=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_by');
});

test('invalid sort_dir value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/categories?sort_dir=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_dir');
});
