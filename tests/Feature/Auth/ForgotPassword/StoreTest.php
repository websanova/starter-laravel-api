<?php

uses()->group('auth.forgot-password.store');

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;

test('forgot password sends reset link for existing user', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('responses.passwords.sent_if_exists')]);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('forgot password returns same response for nonexistent email', function () {
    Notification::fake();

    $response = $this->postJson('/auth/forgot-password', [
        'email' => 'nobody@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('responses.passwords.sent_if_exists')]);

    Notification::assertNothingSent();
});

test('forgot password fails with missing email', function () {
    $response = $this->postJson('/auth/forgot-password', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('forgot password fails with invalid email', function () {
    $response = $this->postJson('/auth/forgot-password', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
