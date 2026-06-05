<?php

uses()->group('admin.user-bookmark.index');

use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;

test('super can list a user\'s bookmarks', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    Bookmark::factory()->count(3)->create(['user_id' => $target->id]);

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}/bookmarks");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'user_id', 'category_id', 'url', 'title', 'description', 'is_favorited', 'created_at', 'updated_at']],
            'meta',
            'links',
        ]);
});

test('admin can list a user\'s bookmarks', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Bookmark::factory()->count(3)->create(['user_id' => $target->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('regular user cannot list a user\'s bookmarks', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/users/{$target->id}/bookmarks");

    $response->assertStatus(403);
});

test('unauthenticated user cannot list a user\'s bookmarks', function () {
    $target = User::factory()->create();

    $response = $this->getJson("/admin/users/{$target->id}/bookmarks");

    $response->assertStatus(401);
});

test('returns only the target user\'s bookmarks', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();

    Bookmark::factory()->count(2)->create(['user_id' => $target->id]);
    Bookmark::factory()->count(3)->create(['user_id' => $other->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('can filter by category', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $target->id]);

    Bookmark::factory()->count(2)->create(['user_id' => $target->id, 'category_id' => $category->id]);
    Bookmark::factory()->create(['user_id' => $target->id, 'category_id' => null]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?category_id={$category->id}");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('can filter uncategorized bookmarks', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $target->id]);

    Bookmark::factory()->count(2)->create(['user_id' => $target->id, 'category_id' => $category->id]);
    Bookmark::factory()->create(['user_id' => $target->id, 'category_id' => null]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?category_id=0");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can filter by favorited', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    Bookmark::factory()->count(2)->create(['user_id' => $target->id, 'is_favorited' => true]);
    Bookmark::factory()->create(['user_id' => $target->id, 'is_favorited' => false]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?favorited=1");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('can set per page limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Bookmark::factory()->count(5)->create(['user_id' => $target->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?per_page=2");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('bookmarks are sorted by created_at descending by default', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Old', 'created_at' => now()->subDays(2)]);
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'New', 'created_at' => now()]);
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Mid', 'created_at' => now()->subDay()]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'New')
        ->assertJsonPath('data.1.title', 'Mid')
        ->assertJsonPath('data.2.title', 'Old');
});

test('bookmarks can be sorted by title ascending', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Zebra']);
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Apple']);
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Mango']);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?sort_by=title&sort_dir=asc");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'Apple')
        ->assertJsonPath('data.1.title', 'Mango')
        ->assertJsonPath('data.2.title', 'Zebra');
});

test('bookmarks can be sorted by title descending', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Zebra']);
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Apple']);
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Mango']);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?sort_by=title&sort_dir=desc");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'Zebra')
        ->assertJsonPath('data.1.title', 'Mango')
        ->assertJsonPath('data.2.title', 'Apple');
});

test('bookmarks can be sorted by created_at ascending', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'Old', 'created_at' => now()->subDays(2)]);
    Bookmark::factory()->create(['user_id' => $target->id, 'title' => 'New', 'created_at' => now()]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?sort_by=created_at&sort_dir=asc");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'Old')
        ->assertJsonPath('data.1.title', 'New');
});

test('invalid sort_by value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?sort_by=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_by');
});

test('invalid sort_dir value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/bookmarks?sort_dir=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_dir');
});
