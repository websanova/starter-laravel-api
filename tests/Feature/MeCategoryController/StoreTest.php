<?php

uses()->group('me.category.store');

use App\Models\User;

test('user can create a category', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/me/categories', [
        'name' => 'Dev Tools',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Dev Tools');
});

test('name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/me/categories', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('name must be a string', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/me/categories', [
        'name' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('name must not exceed 255 characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/me/categories', [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('unauthenticated user cannot create a category', function () {
    $response = $this->postJson('/me/categories', [
        'name' => 'Dev Tools',
    ]);

    $response->assertStatus(401);
});
