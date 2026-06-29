<?php

uses()->group('admin.login.destroy');

use App\Enums\UserRole;
use App\Models\User;

test('admin can logout and token is revoked', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::Admin);
    $token = $user->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/admin/logout');

    $response->assertStatus(204);
    expect($user->tokens()->count())->toBe(0);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/admin/logout');

    $response->assertStatus(401);
});

