<?php

uses()->group('account.bookmark.store');

use App\Models\Category;
use App\Models\User;

test('user can create a bookmark', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.url', 'https://example.com')
        ->assertJsonPath('data.title', 'Example')
        ->assertJsonPath('data.category_id', null);
});

test('user can create a bookmark with a category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'category_id' => $category->id,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.category_id', $category->id);
});

test('user can create a bookmark with a description', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'description' => 'A test bookmark',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.description', 'A test bookmark');
});

test('user cannot assign another user category', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'category_id' => $category->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('category_id');
});

test('url is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'title' => 'Example',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('url');
});

test('title is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('title');
});

test('url must be valid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'not-a-url',
        'title' => 'Example',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('url');
});

test('user can create a bookmark with tags', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'tags' => ['Laravel', 'PHP'],
    ]);

    $response->assertStatus(201)
        ->assertJsonCount(2, 'data.tags')
        ->assertJsonPath('data.tags.0.name', 'laravel')
        ->assertJsonPath('data.tags.1.name', 'php');
});

test('tags are created if they do not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'tags' => ['new-tag'],
    ]);

    $this->assertDatabaseHas('tags', [
        'user_id' => $user->id,
        'name' => 'new-tag',
        'slug' => 'new-tag',
    ]);
});

test('existing tags are reused', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'First',
        'tags' => ['laravel'],
    ]);

    $this->actingAs($user)->postJson('/account/bookmarks', [
        'url' => 'https://example2.com',
        'title' => 'Second',
        'tags' => ['Laravel'],
    ]);

    expect(\App\Models\Tag::where('user_id', $user->id)->where('slug', 'laravel')->count())->toBe(1);
});

test('unauthenticated user cannot create a bookmark', function () {
    $response = $this->postJson('/account/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(401);
});
