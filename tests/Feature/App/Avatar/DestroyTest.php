<?php

uses()->group('app.avatar.destroy');

use App\Enums\VerificationMode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can delete their avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $avatarPath = $user->fresh()->avatar;

    $response = $this->actingAs($user)->deleteJson('/avatar');

    $response->assertStatus(204)
        ->assertNoContent();

    expect($user->fresh()->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($avatarPath);
});

test('deleting avatar when none exists succeeds', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/avatar');

    $response->assertStatus(204)
        ->assertNoContent();
});

test('delete fails when unauthenticated', function () {
    $response = $this->deleteJson('/avatar');

    $response->assertStatus(401);
});

test('delete fails when unverified', function () {
    config(['verification.mode' => VerificationMode::Required]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->deleteJson('/avatar');

    $response->assertStatus(403);
});


