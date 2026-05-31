<?php

uses()->group('me.update');

use App\Models\User;

test('user can update their name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/me', [
        'name' => 'New Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'New Name');
});

test('user can update their email', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/me', [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.email', 'new@example.com');
});

test('update fails with invalid email', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/me', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('update fails with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/me', [
        'email' => 'taken@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('unauthenticated user cannot update profile', function () {
    $response = $this->patchJson('/me', ['name' => 'Test']);

    $response->assertStatus(401);
});
