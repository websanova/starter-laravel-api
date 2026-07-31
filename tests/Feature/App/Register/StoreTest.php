<?php

uses()->group('app.register.store');

use App\Enums\VerificationMode;
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
        ->assertJsonValidationErrors(['first_name', 'email', 'password'])
        ->assertJsonMissingValidationErrors(['last_name']);
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

test('registration leaves locale null when not provided', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'locale' => null,
    ]);
});

test('registration discards an unsupported locale', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'locale' => 'zz',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'locale' => null,
    ]);
});

test('registration discards the default locale so the user follows the configured default', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'locale' => config('user.default_locale'),
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'locale' => null,
    ]);
});

test('registration stores a supported non-default locale', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'locale' => 'fr-CA',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'locale' => 'fr-CA',
    ]);
});

test('registration leaves timezone null when not provided', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'timezone' => null,
    ]);
});

test('registration discards an unsupported timezone', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'timezone' => 'Mars/Olympus_Mons',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'timezone' => null,
    ]);
});

test('registration discards the default timezone so the user follows the configured default', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'timezone' => config('user.default_timezone'),
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'timezone' => null,
    ]);
});

test('registration stores a supported non-default timezone', function () {
    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'timezone' => 'Europe/Paris',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'timezone' => 'Europe/Paris',
    ]);
});

test('registration ignores phone when the phone channel is disabled', function () {
    config(['verification.mode.phone' => VerificationMode::Disabled]);

    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '15551234567',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'phone' => null,
    ]);
});

test('registration requires phone when the phone channel is enabled', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);

    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('registration fails with an invalid phone', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);

    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => 'not-a-phone',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('registration stores phone when the phone channel is enabled', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);

    $this->postJson('/register', [
        'first_name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '15551234567',
    ])->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'phone' => '15551234567',
    ]);
});


