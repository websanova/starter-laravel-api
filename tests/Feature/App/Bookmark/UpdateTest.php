<?php

uses()->group('app.bookmark.update');

use App\Models\Bookmark;
use App\Models\Tag;
use App\Models\User;

test('user can update their bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/bookmarks/{$bookmark->id}", [
        'title' => 'Updated Title',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Title');
});

test('user cannot update another user bookmark', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->putJson("/bookmarks/{$bookmark->id}", [
        'title' => 'Hijacked',
    ]);

    $response->assertStatus(403);
});

test('user can add tags to a bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $tags = Tag::factory()->count(2)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/bookmarks/{$bookmark->id}", [
        'tag_ids' => $tags->pluck('id')->all(),
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data.tags');
});

test('user can replace tags on a bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $oldTags = Tag::factory()->count(2)->create(['user_id' => $user->id]);
    $vue = Tag::factory()->create(['user_id' => $user->id, 'name' => 'vue']);
    $bookmark->tags()->attach($oldTags);

    $response = $this->actingAs($user)->putJson("/bookmarks/{$bookmark->id}", [
        'tag_ids' => [$vue->id],
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.tags')
        ->assertJsonPath('data.tags.0.name', 'vue');
});

test('user can clear tags on a bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $tag = Tag::factory()->create(['user_id' => $user->id]);
    $bookmark->tags()->attach($tag);

    $response = $this->actingAs($user)->putJson("/bookmarks/{$bookmark->id}", [
        'tag_ids' => [],
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data.tags');
});

test('omitting tags leaves them untouched', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);
    $tag = Tag::factory()->create(['user_id' => $user->id, 'name' => 'laravel']);
    $bookmark->tags()->attach($tag);

    $response = $this->actingAs($user)->putJson("/bookmarks/{$bookmark->id}", [
        'title' => 'Updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.tags')
        ->assertJsonPath('data.tags.0.name', 'laravel');
});

test('user cannot attach another user tag', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $other->id]);
    $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/bookmarks/{$bookmark->id}", [
        'tag_ids' => [$tag->id],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('tag_ids.0');
});

test('unauthenticated user cannot update a bookmark', function () {
    $bookmark = Bookmark::factory()->create();

    $response = $this->putJson("/bookmarks/{$bookmark->id}", [
        'title' => 'Updated',
    ]);

    $response->assertStatus(401);
});


