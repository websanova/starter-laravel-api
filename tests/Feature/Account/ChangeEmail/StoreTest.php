<?php

uses()->group('account.change-email.store');

use App\Models\EmailChangeToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can confirm email change with valid token', function () {
    $user = User::factory()->create(['email' => 'old@example.com']);
    $token = 'valid-token-string';

    EmailChangeToken::create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => Hash::make($token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/auth/change-email', [
        'email' => 'new@example.com',
        'token' => $token,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('responses.email_change.confirmed')]);

    $user->refresh();
    expect($user->email)->toBe('new@example.com');
    expect($user->email_verified_at)->not->toBeNull();
});

test('confirm email change deletes tokens', function () {
    $user = User::factory()->create();
    $token = 'valid-token-string';

    EmailChangeToken::create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => Hash::make($token),
        'created_at' => now(),
    ]);

    $this->postJson('/auth/change-email', [
        'email' => 'new@example.com',
        'token' => $token,
    ]);

    expect(EmailChangeToken::where('user_id', $user->id)->count())->toBe(0);
});

test('confirm email change fails with invalid token', function () {
    $user = User::factory()->create();

    EmailChangeToken::create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => Hash::make('real-token'),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/auth/change-email', [
        'email' => 'new@example.com',
        'token' => 'wrong-token',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token']);
});

test('confirm email change fails with expired token', function () {
    $user = User::factory()->create();
    $token = 'valid-token-string';

    EmailChangeToken::create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => Hash::make($token),
        'created_at' => now()->subMinutes(61),
    ]);

    $response = $this->postJson('/auth/change-email', [
        'email' => 'new@example.com',
        'token' => $token,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token']);
});

test('confirm email change fails with mismatched email', function () {
    $user = User::factory()->create();
    $token = 'valid-token-string';

    EmailChangeToken::create([
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => Hash::make($token),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/auth/change-email', [
        'email' => 'other@example.com',
        'token' => $token,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token']);
});

test('confirm email change fails with missing fields', function () {
    $response = $this->postJson('/auth/change-email', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token', 'email']);
});
