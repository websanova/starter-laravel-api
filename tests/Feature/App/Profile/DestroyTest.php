<?php

uses()->group('app.profile.destroy');

use App\Models\User;

test('user can soft delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/profile');

    $response->assertStatus(204);
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('user tokens are revoked on delete', function () {
    $user = User::factory()->create();
    $user->createToken('auth');

    $this->actingAs($user)->deleteJson('/profile');

    expect($user->tokens()->count())->toBe(0);
});

test('unauthenticated user cannot delete account', function () {
    $response = $this->deleteJson('/profile');

    $response->assertStatus(401);
});


