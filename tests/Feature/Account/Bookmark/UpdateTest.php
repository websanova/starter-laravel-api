<?php

uses()->group('account.bookmark.update');

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\User;

test('user can update their bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Title');
});

test('user can update bookmark category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'category_id' => $category->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.category_id', $category->id);
});

test('user can remove bookmark category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $bookmark = Bookmark::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'category_id' => null,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.category_id', null);
});

test('user cannot assign another user category', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $other->id]);
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'category_id' => $category->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('category_id');
});

test('user cannot update another user bookmark', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'title' => 'Hijacked',
    ]);

    $response->assertStatus(403);
});

test('user can add tags to a bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'tags' => ['laravel', 'php'],
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data.tags');
});

test('user can replace tags on a bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $bookmark->syncTags(['laravel', 'php']);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'tags' => ['vue'],
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.tags')
        ->assertJsonPath('data.tags.0.name', 'vue');
});

test('user can clear tags on a bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $bookmark->syncTags(['laravel']);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'tags' => [],
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data.tags');
});

test('omitting tags leaves them untouched', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $bookmark->syncTags(['laravel']);

    $response = $this->actingAs($user)->putJson("/account/bookmarks/{$bookmark->id}", [
        'title' => 'Updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.tags')
        ->assertJsonPath('data.tags.0.name', 'laravel');
});

test('unauthenticated user cannot update a bookmark', function () {
    $bookmark = Bookmark::factory()->create();

    $response = $this->putJson("/account/bookmarks/{$bookmark->id}", [
        'title' => 'Updated',
    ]);

    $response->assertStatus(401);
});

