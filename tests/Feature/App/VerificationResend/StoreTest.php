<?php

uses()->group('app.verification-resend.store');

use App\Enums\VerificationMode;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerificationCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('user can request a new verification code', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/verify/resend', ['channel' => 'email']);

    $response->assertStatus(200);
    Notification::assertSentTo($user, VerificationCodeNotification::class);
});

test('resend creates a new verification code record', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->postJson('/verify/resend', ['channel' => 'email']);

    expect(VerificationCode::where('user_id', $user->id)->count())->toBe(1);
});

test('resend is throttled', function () {
    $user = User::factory()->unverified()->create();

    VerificationCode::create([
        'user_id' => $user->id,
        'channel' => 'email',
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user)->postJson('/verify/resend', ['channel' => 'email']);

    $response->assertStatus(429);
});

test('resend throttle is scoped per channel', function () {
    Notification::fake();
    config(['verification.mode.phone' => VerificationMode::Required]);

    $user = User::factory()->unverified()->create(['phone' => '15551234567']);

    VerificationCode::create([
        'user_id' => $user->id,
        'channel' => 'email',
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user)->postJson('/verify/resend', ['channel' => 'phone']);

    $response->assertStatus(200);
    Notification::assertSentTo($user, VerificationCodeNotification::class);
});

test('resend allowed after throttle period', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $record = VerificationCode::create([
        'user_id' => $user->id,
        'channel' => 'email',
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $record->forceFill([
        'created_at' => now()->subSeconds(config('verification.resend_throttle') + 1),
    ])->save();

    $response = $this->actingAs($user)->postJson('/verify/resend', ['channel' => 'email']);

    $response->assertStatus(200);
    Notification::assertSentTo($user, VerificationCodeNotification::class);
});

test('unauthenticated user cannot resend', function () {
    $response = $this->postJson('/verify/resend', ['channel' => 'email']);

    $response->assertStatus(401);
});

test('validation fails with invalid channel', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/verify/resend', ['channel' => 'carrier-pigeon']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['channel']);
});

test('resend does nothing when mode is disabled', function () {
    Notification::fake();
    config(['verification.mode.email' => VerificationMode::Disabled]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/verify/resend', ['channel' => 'email']);

    $response->assertStatus(200);
    Notification::assertNothingSent();
});
