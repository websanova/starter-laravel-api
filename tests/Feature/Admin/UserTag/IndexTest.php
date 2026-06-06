<?php

uses()->group('admin.user-tag.index');

use App\Enums\UserRole;
use App\Models\Tag;
use App\Models\User;

test('super can list a user\'s tags', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    Tag::factory()->count(3)->create(['user_id' => $target->id]);

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}/tags");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'user_id', 'name', 'slug', 'created_at', 'updated_at']],
        ]);
});

test('admin can list a user\'s tags', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Tag::factory()->count(3)->create(['user_id' => $target->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/tags");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('regular user cannot list a user\'s tags', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/users/{$target->id}/tags");

    $response->assertStatus(403);
});

test('unauthenticated user cannot list a user\'s tags', function () {
    $target = User::factory()->create();

    $response = $this->getJson("/admin/users/{$target->id}/tags");

    $response->assertStatus(401);
});

test('returns only the target user\'s tags', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();

    Tag::factory()->count(2)->create(['user_id' => $target->id]);
    Tag::factory()->count(3)->create(['user_id' => $other->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/tags");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('tags are sorted by name ascending by default', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Tag::factory()->create(['user_id' => $target->id, 'name' => 'zebra']);
    Tag::factory()->create(['user_id' => $target->id, 'name' => 'apple']);
    Tag::factory()->create(['user_id' => $target->id, 'name' => 'mango']);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/tags");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'apple')
        ->assertJsonPath('data.1.name', 'mango')
        ->assertJsonPath('data.2.name', 'zebra');
});

test('tags can be sorted by name descending', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Tag::factory()->create(['user_id' => $target->id, 'name' => 'zebra']);
    Tag::factory()->create(['user_id' => $target->id, 'name' => 'apple']);
    Tag::factory()->create(['user_id' => $target->id, 'name' => 'mango']);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/tags?sort_dir=desc");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'zebra')
        ->assertJsonPath('data.1.name', 'mango')
        ->assertJsonPath('data.2.name', 'apple');
});

test('invalid sort_by value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/tags?sort_by=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_by');
});

test('invalid sort_dir value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/tags?sort_dir=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_dir');
});
