<?php

uses()->group('account.profile.show');

use App\Models\User;

test('authenticated user can view their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/account/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'],
        ]);
});

test('unauthenticated user cannot view profile', function () {
    $response = $this->getJson('/account/profile');

    $response->assertStatus(401);
});
