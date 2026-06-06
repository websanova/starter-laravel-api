<?php

uses()->group('account.tag.destroy');

use App\Models\Tag;
use App\Models\User;

test('user can delete their tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->deleteJson("/account/tags/{$tag->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('user cannot delete another users tag', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $tag = Tag::factory()->for($other)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->deleteJson("/account/tags/{$tag->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a tag', function () {
    $tag = Tag::factory()->create();

    $response = $this->deleteJson("/account/tags/{$tag->id}");

    $response->assertStatus(401);
});
