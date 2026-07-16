<?php

uses()->group('app.avatar.store');

use App\Enums\VerificationMode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can upload an avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['avatar_url'],
        ]);

    expect($user->fresh()->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->fresh()->avatar);
});

test('uploading a new avatar deletes the old one', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('first.jpg'),
    ]);

    $oldAvatar = $user->fresh()->avatar;

    $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('second.jpg'),
    ]);

    Storage::disk('public')->assertMissing($oldAvatar);
    Storage::disk('public')->assertExists($user->fresh()->avatar);
});

test('upload fails without a file', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/avatar', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

test('upload fails with non-image file', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->create('document.pdf', 100),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

test('upload fails with unsupported image type', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.gif'),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

test('upload fails when file exceeds max size', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg')->size(3000),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['avatar']);
});

test('upload fails when unauthenticated', function () {
    $response = $this->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(401);
});

test('upload fails when unverified', function () {
    config(['verification.mode' => VerificationMode::Required]);

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/avatar', [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(403);
});


