<?php

uses()->group('admin.preference.update');

use App\Enums\BookmarkView;
use App\Enums\SortDirection;
use App\Enums\UserRole;
use App\Enums\UserSort;
use App\Models\User;

test('admin can update their preferences', function () {
    $admin = User::factory()->create(['preferences' => null]);
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->patchJson('/admin/preferences', [
        'users_sort_by' => UserSort::Email->value,
        'users_sort_dir' => SortDirection::Asc->value,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.users_sort_by', UserSort::Email->value)
        ->assertJsonPath('data.users_sort_dir', SortDirection::Asc->value);
});

test('updating one preference leaves the others at their defaults', function () {
    $admin = User::factory()->create(['preferences' => null]);
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->patchJson('/admin/preferences', [
        'users_sort_by' => UserSort::Email->value,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.users_sort_by', UserSort::Email->value)
        ->assertJsonPath('data.users_sort_dir', config('user.preferences.admin.users_sort_dir'));
});

test('update does not touch the app scope', function () {
    $admin = User::factory()->create([
        'preferences' => ['app' => ['bookmarks_view' => BookmarkView::Condensed->value]],
    ]);
    $admin->assignRole(UserRole::Admin);

    $this->actingAs($admin)->patchJson('/admin/preferences', [
        'users_sort_by' => UserSort::Email->value,
    ]);

    expect($admin->fresh()->preferences['app'])->toBe([
        'bookmarks_view' => BookmarkView::Condensed->value,
    ]);
});

test('update rejects an invalid sort column', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->patchJson('/admin/preferences', [
        'users_sort_by' => 'nope',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('users_sort_by');
});

test('non admin cannot update admin preferences', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/admin/preferences', [
        'users_sort_by' => UserSort::Email->value,
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot update admin preferences', function () {
    $response = $this->patchJson('/admin/preferences', [
        'users_sort_by' => UserSort::Email->value,
    ]);

    $response->assertStatus(401);
});
