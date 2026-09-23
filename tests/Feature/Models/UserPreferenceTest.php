<?php

uses()->group('model.user.preference');

use App\Enums\PreferenceScope;
use App\Models\User;

test('preferences fall back to the configured defaults when nothing is stored', function () {
    config(['user.preferences.app' => ['bookmarks_view' => 'expanded']]);

    $user = User::factory()->create(['preferences' => null]);

    expect($user->preferencesFor(PreferenceScope::App))->toBe(['bookmarks_view' => 'expanded']);
});

test('stored preferences override the configured defaults', function () {
    config(['user.preferences.app' => ['bookmarks_view' => 'expanded']]);

    $user = User::factory()->create(['preferences' => ['app' => ['bookmarks_view' => 'condensed']]]);

    expect($user->preferencesFor(PreferenceScope::App))->toBe(['bookmarks_view' => 'condensed']);
});

test('a preference added to config appears without touching stored rows', function () {
    $user = User::factory()->create(['preferences' => ['app' => ['bookmarks_view' => 'condensed']]]);

    config(['user.preferences.app' => ['bookmarks_view' => 'expanded', 'bookmarks_sort_by' => 'created_at']]);

    expect($user->preferencesFor(PreferenceScope::App))->toBe([
        'bookmarks_view' => 'condensed',
        'bookmarks_sort_by' => 'created_at',
    ]);
});

test('a preference removed from config is dropped from stored rows', function () {
    $user = User::factory()->create([
        'preferences' => ['app' => ['bookmarks_view' => 'condensed', 'bookmarks_density' => 'tight']],
    ]);

    config(['user.preferences.app' => ['bookmarks_view' => 'expanded']]);

    expect($user->preferencesFor(PreferenceScope::App))->toBe(['bookmarks_view' => 'condensed']);
});

test('each scope resolves only its own preferences', function () {
    config([
        'user.preferences.admin' => ['users_sort_by' => 'created_at'],
        'user.preferences.app' => ['bookmarks_view' => 'expanded'],
    ]);

    $user = User::factory()->create([
        'preferences' => [
            'admin' => ['users_sort_by' => 'email'],
            'app' => ['bookmarks_view' => 'condensed'],
        ],
    ]);

    expect($user->preferencesFor(PreferenceScope::App))->toBe(['bookmarks_view' => 'condensed']);
    expect($user->preferencesFor(PreferenceScope::Admin))->toBe(['users_sort_by' => 'email']);
});

test('setting a preference does not freeze the other defaults into the row', function () {
    config(['user.preferences.app' => ['bookmarks_view' => 'expanded', 'bookmarks_sort_by' => 'created_at']]);

    $user = User::factory()->create(['preferences' => null]);
    $user->setPreferences(PreferenceScope::App, ['bookmarks_view' => 'condensed']);

    expect($user->fresh()->preferences['app'])->toBe(['bookmarks_view' => 'condensed']);

    config(['user.preferences.app' => ['bookmarks_view' => 'expanded', 'bookmarks_sort_by' => 'title']]);

    expect($user->fresh()->preferencesFor(PreferenceScope::App))->toBe([
        'bookmarks_view' => 'condensed',
        'bookmarks_sort_by' => 'title',
    ]);
});

test('setting preferences strips keys that are no longer in config', function () {
    $user = User::factory()->create([
        'preferences' => ['app' => ['bookmarks_view' => 'condensed', 'bookmarks_density' => 'tight']],
    ]);

    config(['user.preferences.app' => ['bookmarks_view' => 'expanded', 'bookmarks_sort_by' => 'created_at']]);

    $user->setPreferences(PreferenceScope::App, ['bookmarks_sort_by' => 'title']);

    expect($user->fresh()->preferences['app'])->toBe([
        'bookmarks_view' => 'condensed',
        'bookmarks_sort_by' => 'title',
    ]);
});
