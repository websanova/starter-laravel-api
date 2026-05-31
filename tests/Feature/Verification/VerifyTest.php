<?php

uses()->group('verification.verify');

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;

test('user can verify with correct code', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    $code = '123456';

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make($code),
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user)->postJson('/verify', [
        'code' => $code,
    ]);

    $response->assertStatus(200);
    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('verification fails with incorrect code', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user)->postJson('/verify', [
        'code' => '000000',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('verification fails with expired code', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)->postJson('/verify', [
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('verification fails after max attempts', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
        'attempts' => config('verification.max_attempts'),
    ]);

    $response = $this->actingAs($user)->postJson('/verify', [
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('unauthenticated user cannot verify', function () {
    $response = $this->postJson('/verify', ['code' => '123456']);

    $response->assertStatus(401);
});
