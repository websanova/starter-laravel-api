<?php

uses()->group('account.reset-password.store');

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

test('user can reset password with valid token', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $response = $this->postJson('/account/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('responses.passwords.reset')]);

    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('reset password revokes existing tokens', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $user->createToken('auth');
    $token = Password::createToken($user);

    $this->postJson('/account/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    expect($user->tokens()->count())->toBe(0);
});

test('reset password fails with invalid token', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/account/reset-password', [
        'token' => 'invalid-token',
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('reset password fails with mismatched confirmation', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $response = $this->postJson('/account/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('reset password fails with missing fields', function () {
    $response = $this->postJson('/account/reset-password', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token', 'email', 'password']);
});

