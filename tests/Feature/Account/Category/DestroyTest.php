<?php

uses()->group('account.category.destroy');

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;

test('user can delete their category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/account/categories/{$category->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('deleting a category nullifies bookmarks', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $bookmark = Bookmark::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($user)->deleteJson("/account/categories/{$category->id}");

    $this->assertDatabaseHas('bookmarks', [
        'id' => $bookmark->id,
        'category_id' => null,
    ]);
});

test('user cannot delete another user category', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->deleteJson("/account/categories/{$category->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a category', function () {
    $category = Category::factory()->create();

    $response = $this->deleteJson("/account/categories/{$category->id}");

    $response->assertStatus(401);
});

