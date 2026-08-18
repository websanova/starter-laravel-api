<?php

uses()->group('app.login.restore');

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Soft-delete restore on login. Drives LoginController::store like StoreTest
// does, kept separate so the grace period cases stay together.

test('soft-deleted user within grace period is restored on login', function () {
    config(['auth.delete.grace_period' => 30]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $user->delete();

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'token']);

    expect($user->fresh()->deleted_at)->toBeNull();
});

test('soft-deleted user past grace period is rejected', function () {
    config(['auth.delete.grace_period' => 30]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $user->delete();
    $user->forceFill(['deleted_at' => now()->subDays(31)])->save();

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    expect($user->fresh()->deleted_at)->not->toBeNull();
});

test('soft-deleted user with zero grace period is rejected', function () {
    config(['auth.delete.grace_period' => 0]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $user->delete();

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    expect($user->fresh()->deleted_at)->not->toBeNull();
});


