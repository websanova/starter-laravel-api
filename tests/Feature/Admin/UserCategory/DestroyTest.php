<?php

uses()->group('admin.user-category.destroy');

use App\Enums\UserRole;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;

test('admin can delete a user\'s category', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/categories/{$category->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('super can delete a user\'s category', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$target->id}/categories/{$category->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('deleting a category nullifies its bookmarks', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $target->id]);
    $bookmark = Bookmark::factory()->create([
        'user_id' => $target->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/categories/{$category->id}");

    $this->assertDatabaseHas('bookmarks', [
        'id' => $bookmark->id,
        'category_id' => null,
    ]);
});

test('regular user cannot delete a user\'s category', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $target->id]);

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/categories/{$category->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a user\'s category', function () {
    $target = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $target->id]);

    $response = $this->deleteJson("/admin/users/{$target->id}/categories/{$category->id}");

    $response->assertStatus(401);
});

test('cannot delete a category belonging to a different user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/categories/{$category->id}");

    $response->assertStatus(404);
});
