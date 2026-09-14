<?php

uses()->group('app.bookmark.store');

use App\Models\Bookmark;
use App\Models\Tag;
use App\Models\User;

test('user can create a bookmark', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.url', 'https://example.com')
        ->assertJsonPath('data.title', 'Example');
});

test('user can create a bookmark with a description', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'description' => 'A test bookmark',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.description', 'A test bookmark');
});

test('url is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'title' => 'Example',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('url');
});

test('title is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('title');
});

test('url must be valid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'not-a-url',
        'title' => 'Example',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('url');
});

test('url must be unique for the user', function () {
    $user = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $user->id, 'url' => 'https://example.com']);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('url');
});

test('normalized url variant counts as a duplicate', function () {
    $user = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $user->id, 'url' => 'https://example.com/path?a=1&b=2']);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://Example.com:443/path/?b=2&a=1#section',
        'title' => 'Example',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('url');
});

test('user can save a url another user already saved', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Bookmark::factory()->create(['user_id' => $other->id, 'url' => 'https://example.com']);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(201);
});

test('user can create a bookmark with tags', function () {
    $user = User::factory()->create();
    $laravel = Tag::factory()->create(['user_id' => $user->id, 'name' => 'laravel']);
    $php = Tag::factory()->create(['user_id' => $user->id, 'name' => 'php']);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'tag_ids' => [$laravel->id, $php->id],
    ]);

    $response->assertStatus(201)
        ->assertJsonCount(2, 'data.tags')
        ->assertJsonPath('data.tags.0.name', 'laravel')
        ->assertJsonPath('data.tags.1.name', 'php');
});

test('user cannot attach another user tag', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'tag_ids' => [$tag->id],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('tag_ids.0');
});

test('user can attach up to the max tags', function () {
    $user = User::factory()->create();
    $tags = Tag::factory()->count(config('bookmark.max_tags'))->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'tag_ids' => $tags->pluck('id')->all(),
    ]);

    $response->assertStatus(201)
        ->assertJsonCount(config('bookmark.max_tags'), 'data.tags');
});

test('user cannot attach more than the max tags', function () {
    $user = User::factory()->create();
    $tags = Tag::factory()->count(config('bookmark.max_tags') + 1)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'tag_ids' => $tags->pluck('id')->all(),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('tag_ids');
});

test('tag ids must be distinct', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
        'tag_ids' => [$tag->id, $tag->id],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('tag_ids.1');
});

test('unauthenticated user cannot create a bookmark', function () {
    $response = $this->postJson('/bookmarks', [
        'url' => 'https://example.com',
        'title' => 'Example',
    ]);

    $response->assertStatus(401);
});


