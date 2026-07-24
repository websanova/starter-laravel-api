<?php

uses()->group('app.verification.mode');

use App\Enums\VerificationMode;
use App\Models\User;
use App\Notifications\VerificationCodeNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;

test('required mode does not send verification on register', function () {
    Notification::fake();
    config(['verification.mode.email' => VerificationMode::Required]);

    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $user = User::where('email', 'test@example.com')->first();
    expect($user->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, WelcomeNotification::class);
    Notification::assertNotSentTo($user, VerificationCodeNotification::class);
});

test('auto mode verifies user immediately on register', function () {
    Notification::fake();
    config(['verification.mode.email' => VerificationMode::Auto]);

    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $user = User::where('email', 'test@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
    Notification::assertSentTo($user, WelcomeNotification::class);
    Notification::assertNotSentTo($user, VerificationCodeNotification::class);
});

test('disabled mode skips verification on register', function () {
    Notification::fake();
    config(['verification.mode.email' => VerificationMode::Disabled]);

    $response = $this->postJson('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);

    $user = User::where('email', 'test@example.com')->first();
    expect($user->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, WelcomeNotification::class);
    Notification::assertNotSentTo($user, VerificationCodeNotification::class);
});

test('required mode blocks unverified user from protected routes', function () {
    config(['verification.mode.email' => VerificationMode::Required]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(403);
});

test('required mode allows verified user to access protected routes', function () {
    config(['verification.mode.email' => VerificationMode::Required]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200);
});

test('disabled mode allows unverified user to access protected routes', function () {
    config(['verification.mode.email' => VerificationMode::Disabled]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200);
});

test('auto mode allows all users to access protected routes', function () {
    config(['verification.mode.email' => VerificationMode::Auto]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200);
});

test('grace period allows unverified user within window', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => 60]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200);
});

test('grace period blocks unverified user after window expires', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => 60]);

    $user = User::factory()->unverified()->create();
    $user->forceFill(['created_at' => now()->subMinutes(61)])->save();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(403);
});

test('no grace period blocks unverified user immediately', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => null]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(403);
});

test('phone required does not block user without phone', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200);
});

test('phone required blocks user with unverified phone', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);

    $user = User::factory()->create(['phone' => '15551234567']);

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(403);
});


