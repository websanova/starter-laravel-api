<?php

uses()->group('app.profile.update');

use App\Models\User;

test('user can update their first name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'first_name' => 'New',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.first_name', 'New');
});

test('user can update their last name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'last_name' => 'Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.last_name', 'Name');
});

test('user can update their locale', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'locale' => 'en',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.locale', 'en');
});

test('user can update their timezone', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'timezone' => 'Europe/London',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.timezone', 'Europe/London');
});

test('clearing timezone falls back to the default', function () {
    $user = User::factory()->create(['timezone' => 'Europe/London']);

    $response = $this->actingAs($user)->patchJson('/profile', [
        'timezone' => null,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.timezone', config('auth.user.default_timezone'));

    expect($user->fresh()->getRawOriginal('timezone'))->toBeNull();
});

test('update rejects an invalid timezone', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'timezone' => 'Not/AZone',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('timezone');
});

test('update rejects an unsupported locale', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', [
        'locale' => 'zz',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('locale');
});

test('update ignores email field', function () {
    $user = User::factory()->create(['email' => 'original@example.com']);

    $response = $this->actingAs($user)->patchJson('/profile', [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.email', 'original@example.com');
});

test('unauthenticated user cannot update profile', function () {
    $response = $this->patchJson('/profile', ['first_name' => 'Test']);

    $response->assertStatus(401);
});


