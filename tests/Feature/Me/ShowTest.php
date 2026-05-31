<?php

uses()->group('me.show');

use App\Models\User;

test('authenticated user can view their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'created_at', 'updated_at'],
        ]);
});

test('unauthenticated user cannot view profile', function () {
    $response = $this->getJson('/me');

    $response->assertStatus(401);
});
