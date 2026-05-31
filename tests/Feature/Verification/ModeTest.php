<?php

uses()->group('verification.mode');

use App\Models\User;
use App\Notifications\VerificationCodeNotification;
use Illuminate\Support\Facades\Notification;

test('required mode sends verification on register', function () {
    Notification::fake();
    config(['verification.mode' => 'required']);

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
    Notification::assertSentTo($user, VerificationCodeNotification::class);
});

test('auto mode verifies user immediately on register', function () {
    Notification::fake();
    config(['verification.mode' => 'auto']);

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
    Notification::assertNothingSent();
});

test('disabled mode skips verification on register', function () {
    Notification::fake();
    config(['verification.mode' => 'disabled']);

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
    Notification::assertNothingSent();
});

test('required mode blocks unverified user from protected routes', function () {
    config(['verification.mode' => 'required']);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/me');

    $response->assertStatus(403)
        ->assertJson(['message' => 'Your email address is not verified.']);
});

test('required mode allows verified user to access protected routes', function () {
    config(['verification.mode' => 'required']);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/me');

    $response->assertStatus(200);
});

test('disabled mode allows unverified user to access protected routes', function () {
    config(['verification.mode' => 'disabled']);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/me');

    $response->assertStatus(200);
});

test('auto mode allows all users to access protected routes', function () {
    config(['verification.mode' => 'auto']);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/me');

    $response->assertStatus(200);
});
