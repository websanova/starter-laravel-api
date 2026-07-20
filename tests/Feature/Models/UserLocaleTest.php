<?php

uses()->group('model.user.locale');

use App\Models\User;

test('preferred locale returns the base language when the stored tag has no exact translation', function () {
    config(['app.supported_locales' => ['en'], 'app.fallback_locale' => 'en']);

    $user = User::factory()->create(['locale' => 'en-US']);

    expect($user->preferredLocale())->toBe('en');
});

test('preferred locale returns the exact translation locale when one exists for the tag', function () {
    config(['app.supported_locales' => ['en', 'en_CA'], 'app.fallback_locale' => 'en']);

    $user = User::factory()->create(['locale' => 'en-CA']);

    expect($user->preferredLocale())->toBe('en_CA');
});

test('preferred locale falls back when neither the tag nor its base language is supported', function () {
    config(['app.supported_locales' => ['en'], 'app.fallback_locale' => 'en']);

    $user = User::factory()->create(['locale' => 'fr-FR']);

    expect($user->preferredLocale())->toBe('en');
});
