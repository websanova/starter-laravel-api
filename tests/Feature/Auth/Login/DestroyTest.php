<?php

uses()->group('auth.login.destroy');

use App\Models\User;

test('user can logout and token is revoked', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/auth/logout');

    $response->assertStatus(204);
    expect($user->tokens()->count())->toBe(0);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/auth/logout');

    $response->assertStatus(401);
});
