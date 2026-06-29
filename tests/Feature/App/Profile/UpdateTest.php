<?php

uses()->group('app.profile.update');

use App\Models\User;

test('user can update their first name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'first_name' => 'New',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.first_name', 'New');
});

test('user can update their last name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'last_name' => 'Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.last_name', 'Name');
});

test('update ignores email field', function () {
    $user = User::factory()->create(['email' => 'original@example.com']);

    $response = $this->actingAs($user)->patchJson('/profile', [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.email', 'original@example.com');
});

test('unauthenticated user cannot update profile', function () {
    $response = $this->patchJson('/profile', ['first_name' => 'Test']);

    $response->assertStatus(401);
});


