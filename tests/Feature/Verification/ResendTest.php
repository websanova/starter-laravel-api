<?php

uses()->group('verification.resend');

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificationCodeNotification;

test('user can request a new verification code', function () {
    Notification::fake();

    $user = User::factory()->create(['email_verified_at' => null]);

    $response = $this->actingAs($user)->postJson('/verify/resend');

    $response->assertStatus(200);
    Notification::assertSentTo($user, VerificationCodeNotification::class);
});

test('resend is throttled', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => 'hashed',
        'expires_at' => now()->addMinutes(15),
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson('/verify/resend');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('unauthenticated user cannot resend', function () {
    $response = $this->postJson('/verify/resend');

    $response->assertStatus(401);
});
