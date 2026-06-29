<?php

uses()->group('admin.login.update');

use App\Enums\UserRole;
use App\Models\User;

test('admin can refresh their token', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin);
    $oldToken = $user->createToken('auth');

    $response = $this->withHeader('Authorization', 'Bearer ' . $oldToken->plainTextToken)
        ->postJson('/admin/auth/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);

    expect($user->tokens()->where('id', $oldToken->accessToken->id)->count())->toBe(0);
    expect($user->tokens()->count())->toBe(1);
});

test('refresh returns a different token', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin);
    $oldToken = $user->createToken('auth');

    $response = $this->withHeader('Authorization', 'Bearer ' . $oldToken->plainTextToken)
        ->postJson('/admin/auth/refresh');

    expect($response->json('token'))->not->toBe($oldToken->plainTextToken);
});

test('unauthenticated user cannot refresh', function () {
    $response = $this->postJson('/admin/auth/refresh');

    $response->assertStatus(401);
});
