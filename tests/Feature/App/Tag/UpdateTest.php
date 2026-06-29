<?php

uses()->group('app.tag.update');

use App\Models\Tag;
use App\Models\User;

test('user can update their tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'laravl']);

    $response = $this->actingAs($user)->putJson("/tags/{$tag->id}", [
        'name' => 'Laravel',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'laravel')
        ->assertJsonPath('data.slug', 'laravel');
});

test('user cannot update another users tag', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $tag = Tag::factory()->for($other)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->putJson("/tags/{$tag->id}", [
        'name' => 'vue',
    ]);

    $response->assertStatus(403);
});

test('name must be a string', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->putJson("/tags/{$tag->id}", [
        'name' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('name must not exceed 50 characters', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->putJson("/tags/{$tag->id}", [
        'name' => str_repeat('a', 51),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('unauthenticated user cannot update a tag', function () {
    $tag = Tag::factory()->create();

    $response = $this->putJson("/tags/{$tag->id}", [
        'name' => 'vue',
    ]);

    $response->assertStatus(401);
});


