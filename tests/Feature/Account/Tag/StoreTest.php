<?php

uses()->group('account.tag.store');

use App\Models\Tag;
use App\Models\User;

test('user can create a tag', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/tags', [
        'name' => 'Laravel',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'laravel')
        ->assertJsonPath('data.slug', 'laravel');
});

test('name is lowercased and trimmed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/tags', [
        'name' => '  Vue JS  ',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'vue js')
        ->assertJsonPath('data.slug', 'vue-js');
});

test('duplicate slug for same user is rejected', function () {
    $user = User::factory()->create();

    Tag::factory()->for($user)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->postJson('/account/tags', [
        'name' => 'Laravel',
    ]);

    $response->assertStatus(500);
});

test('different users can have the same tag name', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Tag::factory()->for($user1)->create(['name' => 'laravel']);

    $response = $this->actingAs($user2)->postJson('/account/tags', [
        'name' => 'laravel',
    ]);

    $response->assertStatus(201);
});

test('name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/tags', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('name must be a string', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/tags', [
        'name' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('name must not exceed 50 characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/tags', [
        'name' => str_repeat('a', 51),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('unauthenticated user cannot create a tag', function () {
    $response = $this->postJson('/account/tags', [
        'name' => 'laravel',
    ]);

    $response->assertStatus(401);
});
