<?php

uses()->group('me-password.update');

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can update their password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->patchJson('/me/password', [
        'current_password' => 'current-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('responses.password.updated')]);

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

test('update fails with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->patchJson('/me/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

test('update fails without current password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/me/password', [
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

test('update fails without password confirmation', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->patchJson('/me/password', [
        'current_password' => 'current-password',
        'password' => 'new-password-123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('update fails when password confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->patchJson('/me/password', [
        'current_password' => 'current-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('update fails when unauthenticated', function () {
    $response = $this->patchJson('/me/password', [
        'current_password' => 'current-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertStatus(401);
});

test('update fails when unverified', function () {
    $user = User::factory()->unverified()->create([
        'password' => Hash::make('current-password'),
    ]);

    $response = $this->actingAs($user)->patchJson('/me/password', [
        'current_password' => 'current-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertStatus(403);
});
