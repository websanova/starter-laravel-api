<?php

uses()->group('app.preference.update');

use App\Enums\BookmarkSort;
use App\Enums\BookmarkView;
use App\Enums\SortDirection;
use App\Enums\UserSort;
use App\Models\User;

test('user can update their preferences', function () {
    $user = User::factory()->create(['preferences' => null]);

    $response = $this->actingAs($user)->patchJson('/preferences', [
        'bookmarks_sort_by' => BookmarkSort::Title->value,
        'bookmarks_sort_dir' => SortDirection::Asc->value,
        'bookmarks_view' => BookmarkView::Condensed->value,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.bookmarks_sort_by', BookmarkSort::Title->value)
        ->assertJsonPath('data.bookmarks_sort_dir', SortDirection::Asc->value)
        ->assertJsonPath('data.bookmarks_view', BookmarkView::Condensed->value);
});

test('updating one preference leaves the others at their defaults', function () {
    $user = User::factory()->create(['preferences' => null]);

    $response = $this->actingAs($user)->patchJson('/preferences', [
        'bookmarks_view' => BookmarkView::Condensed->value,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.bookmarks_view', BookmarkView::Condensed->value)
        ->assertJsonPath('data.bookmarks_sort_by', config('user.preferences.app.bookmarks_sort_by'))
        ->assertJsonPath('data.bookmarks_sort_dir', config('user.preferences.app.bookmarks_sort_dir'));
});

test('update only stores the preferences the user has set', function () {
    $user = User::factory()->create(['preferences' => null]);

    $this->actingAs($user)->patchJson('/preferences', [
        'bookmarks_view' => BookmarkView::Condensed->value,
    ]);

    expect($user->fresh()->preferences['app'])->toBe([
        'bookmarks_view' => BookmarkView::Condensed->value,
    ]);
});

test('update merges into preferences the user already set', function () {
    $user = User::factory()->create([
        'preferences' => ['app' => ['bookmarks_view' => BookmarkView::Condensed->value]],
    ]);

    $response = $this->actingAs($user)->patchJson('/preferences', [
        'bookmarks_sort_by' => BookmarkSort::Title->value,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.bookmarks_view', BookmarkView::Condensed->value)
        ->assertJsonPath('data.bookmarks_sort_by', BookmarkSort::Title->value);
});

test('update does not touch the admin scope', function () {
    $user = User::factory()->create([
        'preferences' => ['admin' => ['users_sort_by' => UserSort::Email->value]],
    ]);

    $this->actingAs($user)->patchJson('/preferences', [
        'bookmarks_view' => BookmarkView::Condensed->value,
    ]);

    expect($user->fresh()->preferences['admin'])->toBe([
        'users_sort_by' => UserSort::Email->value,
    ]);
});

test('update rejects an unknown preference', function () {
    $user = User::factory()->create(['preferences' => null]);

    $this->actingAs($user)->patchJson('/preferences', [
        'users_sort_by' => UserSort::Email->value,
    ]);

    expect($user->fresh()->preferences['app'] ?? [])->toBe([]);
});

test('update rejects an invalid view', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/preferences', [
        'bookmarks_view' => 'sideways',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('bookmarks_view');
});

test('update rejects an invalid sort column', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/preferences', [
        'bookmarks_sort_by' => 'nope',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('bookmarks_sort_by');
});

test('unauthenticated user cannot update preferences', function () {
    $response = $this->patchJson('/preferences', [
        'bookmarks_view' => BookmarkView::Condensed->value,
    ]);

    $response->assertStatus(401);
});
