<?php

uses()->group('account.verification.verify');

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;

test('user can verify with correct code', function () {
    $user = User::factory()->unverified()->create();
    $code = '123456';

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make($code),
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user)->postJson('/account/verify', [
        'code' => $code,
    ]);

    $response->assertStatus(200);
    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('verification fails with incorrect code', function () {
    $user = User::factory()->unverified()->create();

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $response = $this->actingAs($user)->postJson('/account/verify', [
        'code' => '000000',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('incorrect attempt increments attempts counter', function () {
    $user = User::factory()->unverified()->create();

    $record = VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
    ]);

    $this->actingAs($user)->postJson('/account/verify', [
        'code' => '000000',
    ]);

    expect($record->fresh()->attempts)->toBe(1);
});

test('verification fails with expired code', function () {
    $user = User::factory()->unverified()->create();

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($user)->postJson('/account/verify', [
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('verification fails after max attempts', function () {
    $user = User::factory()->unverified()->create();

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
        'attempts' => config('verification.max_attempts'),
    ]);

    $response = $this->actingAs($user)->postJson('/account/verify', [
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('verification fails with already verified code', function () {
    $user = User::factory()->unverified()->create();

    VerificationCode::create([
        'user_id' => $user->id,
        'code' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(15),
        'verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson('/account/verify', [
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('unauthenticated user cannot verify', function () {
    $response = $this->postJson('/account/verify', ['code' => '123456']);

    $response->assertStatus(401);
});

test('validation fails with missing code', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/account/verify', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});

test('validation fails with wrong length code', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/account/verify', [
        'code' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['code']);
});
