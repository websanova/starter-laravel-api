<?php

uses()->group('middleware.set-locale');

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * The invalid password path returns the translated responses.auth.failed
 * message, which makes it a convenient probe for the resolved locale.
 */
function postLoginWithLocale($test, ?string $acceptLanguage): \Illuminate\Testing\TestResponse
{
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $headers = $acceptLanguage ? ['Accept-Language' => $acceptLanguage] : [];

    return $test->withHeaders($headers)->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);
}

test('a supported regional locale resolves and returns translated messages', function () {
    postLoginWithLocale($this, 'fr-CA')
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', __('responses.auth.failed', [], 'fr_CA'));
});

test('a region variant falls through to its base language translations', function () {
    postLoginWithLocale($this, 'fr-FR')
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', __('responses.auth.failed', [], 'fr_CA'));
});

test('an unsupported locale falls back to the first supported locale', function () {
    postLoginWithLocale($this, 'de')
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', __('responses.auth.failed', [], 'en'));
});

test('a missing Accept-Language header falls back to the first supported locale', function () {
    postLoginWithLocale($this, null)
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', __('responses.auth.failed', [], 'en'));
});
