<?php

uses()->group('account.login.update');

use App\Models\User;

test('user can refresh their token', function () {
    $user = User::factory()->create();
    $oldToken = $user->createToken('auth');

    $response = $this->withHeader('Authorization', 'Bearer ' . $oldToken->plainTextToken)
        ->postJson('/account/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);

    // Old token should be deleted
    expect($user->tokens()->where('id', $oldToken->accessToken->id)->count())->toBe(0);

    // New token should exist
    expect($user->tokens()->count())->toBe(1);
});

test('refresh returns a different token', function () {
    $user = User::factory()->create();
    $oldToken = $user->createToken('auth');

    $response = $this->withHeader('Authorization', 'Bearer ' . $oldToken->plainTextToken)
        ->postJson('/account/refresh');

    $newToken = $response->json('token');
    expect($newToken)->not->toBe($oldToken->plainTextToken);
});

test('unauthenticated user cannot refresh', function () {
    $response = $this->postJson('/account/refresh');

    $response->assertStatus(401);
});

