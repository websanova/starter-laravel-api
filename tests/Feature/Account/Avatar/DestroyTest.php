<?php

uses()->group('account.avatar.destroy');

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can delete their avatar', function () {
    Storage::fake('s3');
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/account/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $avatarPath = $user->fresh()->avatar;

    $response = $this->actingAs($user)->deleteJson('/account/avatar');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'email', 'avatar_url', 'created_at', 'updated_at'],
        ])
        ->assertJsonPath('data.avatar_url', null);

    expect($user->fresh()->avatar)->toBeNull();
    Storage::disk('s3')->assertMissing($avatarPath);
});

test('deleting avatar when none exists returns user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/account/avatar');

    $response->assertStatus(200)
        ->assertJsonPath('data.avatar_url', null);
});

test('delete fails when unauthenticated', function () {
    $response = $this->deleteJson('/account/avatar');

    $response->assertStatus(401);
});

test('delete fails when unverified', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->deleteJson('/account/avatar');

    $response->assertStatus(403);
});

