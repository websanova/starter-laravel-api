<?php

uses()->group('account.verification.resend');

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerificationCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('user can request a new verification code', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/account/verify/resend');

    $response->assertStatus(200);
    Notification::assertSentTo($user, VerificationCodeNotification::class);
});

test('resend creates a new verification code record', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->postJson('/account/verify/resend');

    expect(VerificationCode::where('user_id', $user->id)->count())->toBe(1);
});

test('resend is throttled', function () {
    $user = User::factory()->unverified()->create();

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user)->postJson('/account/verify/resend');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('resend allowed after throttle period', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $record = VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $record->forceFill([
        'created_at' => now()->subSeconds(config('verification.resend_throttle') + 1),
    ])->save();

    $response = $this->actingAs($user)->postJson('/account/verify/resend');

    $response->assertStatus(200);
    Notification::assertSentTo($user, VerificationCodeNotification::class);
});

test('unauthenticated user cannot resend', function () {
    $response = $this->postJson('/account/verify/resend');

    $response->assertStatus(401);
});

test('resend does nothing when mode is disabled', function () {
    Notification::fake();
    config(['verification.mode' => \App\Enums\VerificationMode::Disabled]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/account/verify/resend');

    $response->assertStatus(200);
    Notification::assertNothingSent();
});
