<?php

uses()->group('account.bookmark.index');

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;

test('user can list their bookmarks', function () {
    $user = User::factory()->create();
    Bookmark::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/account/bookmarks');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'category_id', 'url', 'title', 'description', 'created_at', 'updated_at']],
            'meta',
            'links',
        ]);
});

test('user only sees their own bookmarks', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Bookmark::factory()->count(2)->create(['user_id' => $user->id]);
    Bookmark::factory()->count(3)->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->getJson('/account/bookmarks');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('user can filter bookmarks by category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    Bookmark::factory()->count(2)->create(['user_id' => $user->id, 'category_id' => $category->id]);
    Bookmark::factory()->create(['user_id' => $user->id, 'category_id' => null]);

    $response = $this->actingAs($user)->getJson("/account/bookmarks?category_id={$category->id}");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('user can filter uncategorized bookmarks', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    Bookmark::factory()->count(2)->create(['user_id' => $user->id, 'category_id' => $category->id]);
    Bookmark::factory()->create(['user_id' => $user->id, 'category_id' => null]);

    $response = $this->actingAs($user)->getJson('/account/bookmarks?category_id=0');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can set per page limit', function () {
    $user = User::factory()->create();
    Bookmark::factory()->count(5)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/account/bookmarks?per_page=2');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('unauthenticated user cannot list bookmarks', function () {
    $response = $this->getJson('/account/bookmarks');

    $response->assertStatus(401);
});
