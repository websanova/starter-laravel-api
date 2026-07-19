<?php

uses()->group('app.bookmark.index');

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;

test('user can list their bookmarks', function () {
    $user = User::factory()->create();
    Bookmark::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'category_id', 'url', 'title', 'description', 'created_at', 'updated_at']],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('user only sees their own bookmarks', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Bookmark::factory()->count(2)->create(['user_id' => $user->id]);
    Bookmark::factory()->count(3)->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('user can filter bookmarks by category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    Bookmark::factory()->count(2)->create(['user_id' => $user->id, 'category_id' => $category->id]);
    Bookmark::factory()->create(['user_id' => $user->id, 'category_id' => null]);

    $response = $this->actingAs($user)->getJson("/bookmarks?category_id={$category->id}");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('user can filter uncategorized bookmarks', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    Bookmark::factory()->count(2)->create(['user_id' => $user->id, 'category_id' => $category->id]);
    Bookmark::factory()->create(['user_id' => $user->id, 'category_id' => null]);

    $response = $this->actingAs($user)->getJson('/bookmarks?category_id=0');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can set per page limit', function () {
    $user = User::factory()->create();
    Bookmark::factory()->count(5)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/bookmarks?per_page=2');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('bookmarks are sorted by created_at descending by default', function () {
    $user = User::factory()->create();
    $old = Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Old', 'created_at' => now()->subDays(2)]);
    $new = Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'New', 'created_at' => now()]);
    $mid = Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Mid', 'created_at' => now()->subDay()]);

    $response = $this->actingAs($user)->getJson('/bookmarks');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'New')
        ->assertJsonPath('data.1.title', 'Mid')
        ->assertJsonPath('data.2.title', 'Old');
});

test('bookmarks can be sorted by title ascending', function () {
    $user = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Zebra']);
    Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Apple']);
    Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Mango']);

    $response = $this->actingAs($user)->getJson('/bookmarks?sort_by=title&sort_dir=asc');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'Apple')
        ->assertJsonPath('data.1.title', 'Mango')
        ->assertJsonPath('data.2.title', 'Zebra');
});

test('bookmarks can be sorted by title descending', function () {
    $user = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Zebra']);
    Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Apple']);
    Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Mango']);

    $response = $this->actingAs($user)->getJson('/bookmarks?sort_by=title&sort_dir=desc');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'Zebra')
        ->assertJsonPath('data.1.title', 'Mango')
        ->assertJsonPath('data.2.title', 'Apple');
});

test('bookmarks can be sorted by created_at ascending', function () {
    $user = User::factory()->create();
    $old = Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'Old', 'created_at' => now()->subDays(2)]);
    $new = Bookmark::factory()->create(['user_id' => $user->id, 'title' => 'New', 'created_at' => now()]);

    $response = $this->actingAs($user)->getJson('/bookmarks?sort_by=created_at&sort_dir=asc');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.title', 'Old')
        ->assertJsonPath('data.1.title', 'New');
});

test('invalid sort_by value returns validation error', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/bookmarks?sort_by=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_by');
});

test('invalid sort_dir value returns validation error', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/bookmarks?sort_dir=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_dir');
});

test('unauthenticated user cannot list bookmarks', function () {
    $response = $this->getJson('/bookmarks');

    $response->assertStatus(401);
});


