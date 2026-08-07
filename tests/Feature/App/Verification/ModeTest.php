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
    Notification::assertNotSentTo($user, WelcomeNotification::class);
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

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(403);
});

test('required mode allows verified user to access protected routes', function () {
    config(['verification.mode.email' => VerificationMode::Required]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('disabled mode allows unverified user to access protected routes', function () {
    config(['verification.mode.email' => VerificationMode::Disabled]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('auto mode allows all users to access protected routes', function () {
    config(['verification.mode.email' => VerificationMode::Auto]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('grace period allows unverified user within window', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => 60]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('grace period blocks unverified user after window expires', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => 60]);

    $user = User::factory()->unverified()->create();
    $user->forceFill(['created_at' => now()->subMinutes(61)])->save();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(403);
});

test('no grace period blocks unverified user immediately', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => null]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(403);
});

test('phone required does not block user without phone', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('phone required blocks user with unverified phone', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);
    config(['verification.grace_period.phone' => null]);

    $user = User::factory()->create(['phone' => '15551234567']);

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(403);
});

test('phone grace period allows unverified phone within window', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);
    config(['verification.grace_period.phone' => 600]);

    $user = User::factory()->create(['phone' => '15551234567']);

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('phone grace period blocks unverified phone after window expires', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);
    config(['verification.grace_period.phone' => 600]);

    $user = User::factory()->create(['phone' => '15551234567']);
    $user->forceFill(['created_at' => now()->subMinutes(601)])->save();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(403);
});

test('grace period blocks at the exact window boundary', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => 60]);

    $user = User::factory()->unverified()->create();
    $user->forceFill(['created_at' => now()->subMinutes(60)])->save();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(403);
});

test('phone auto mode allows user with unverified phone', function () {
    config(['verification.mode.phone' => VerificationMode::Auto]);

    $user = User::factory()->create(['phone' => '15551234567']);

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('phone disabled mode allows user with unverified phone', function () {
    config(['verification.mode.phone' => VerificationMode::Disabled]);

    $user = User::factory()->create(['phone' => '15551234567']);

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
});

test('staggered channels block once both grace periods expire', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.mode.phone' => VerificationMode::Required]);
    config(['verification.grace_period.email' => null]);
    config(['verification.grace_period.phone' => 600]);

    $user = User::factory()->unverified()->create(['phone' => '15551234567']);
    $user->forceFill(['created_at' => now()->subMinutes(601)])->save();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(403);
});

test('pending and required diverge within the grace window', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => 60]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.verification_pending', ['email'])
        ->assertJsonPath('data.verification_required', [])
        ->assertJsonPath('data.is_verification_pending', true)
        ->assertJsonPath('data.is_verification_required', false);
});

test('pending and required match once the grace window expires', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.grace_period.email' => 60]);

    $user = User::factory()->unverified()->create();
    $user->forceFill(['created_at' => now()->subMinutes(61)])->save();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.verification_pending', ['email'])
        ->assertJsonPath('data.verification_required', ['email'])
        ->assertJsonPath('data.is_verification_pending', true)
        ->assertJsonPath('data.is_verification_required', true);
});

test('channel without an identifier is never pending', function () {
    config(['verification.mode.phone' => VerificationMode::Required]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.verification_pending', []);
});

test('verified channel drops out of pending', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.mode.phone' => VerificationMode::Required]);

    $user = User::factory()->create(['phone' => '15551234567']);

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.verification_pending', ['phone']);
});

test('staggered channels report both pending but only one required', function () {
    config(['verification.mode.email' => VerificationMode::Required]);
    config(['verification.mode.phone' => VerificationMode::Required]);
    config(['verification.grace_period.email' => null]);
    config(['verification.grace_period.phone' => 600]);

    $user = User::factory()->unverified()->create(['phone' => '15551234567']);

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.verification_pending', ['email', 'phone'])
        ->assertJsonPath('data.verification_required', ['email']);
});


