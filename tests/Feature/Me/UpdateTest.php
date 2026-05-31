<?php

uses()->group('me.update');

use App\Models\User;

test('user can update their first name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/me', [
        'first_name' => 'New',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.first_name', 'New');
});

test('user can update their last name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/me', [
        'last_name' => 'Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.last_name', 'Name');
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
    $response = $this->patchJson('/me', ['first_name' => 'Test']);

    $response->assertStatus(401);
});
