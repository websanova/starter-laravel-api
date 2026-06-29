<?php

uses()->group('app.register.store');

use App\Models\User;

test('user can register with valid data', function () {
    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('registration fails with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('registration fails with missing fields', function () {
    $response = $this->postJson('/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password']);
});

test('registration fails with mismatched password confirmation', function () {
    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});


