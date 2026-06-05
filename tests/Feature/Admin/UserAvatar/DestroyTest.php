<?php

uses()->group('admin.user-avatar.destroy');

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin can delete a user avatar', function () {
    Storage::fake('s3');
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->storeAvatar(UploadedFile::fake()->image('avatar.png'));
    $avatarPath = $target->fresh()->avatar;

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/avatar");

    $response->assertStatus(200)
        ->assertJsonPath('data.avatar_url', null);

    expect($target->fresh()->avatar)->toBeNull();
    Storage::disk('s3')->assertMissing($avatarPath);
});

test('deleting avatar when none exists returns user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/avatar");

    $response->assertStatus(200)
        ->assertJsonPath('data.avatar_url', null);
});

test('admin cannot delete a super user avatar', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$super->id}/avatar");

    $response->assertStatus(403);
});

test('regular user cannot delete another user avatar', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/avatar");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a user avatar', function () {
    $target = User::factory()->create();

    $response = $this->deleteJson("/admin/users/{$target->id}/avatar");

    $response->assertStatus(401);
});
