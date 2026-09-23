<?php

uses()->group('app.profile.show');

use App\Enums\UserSort;
use App\Models\User;

test('authenticated user can view their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'],
        ]);
});

test('profile carries the app preferences and none from the admin scope', function () {
    $user = User::factory()->create([
        'preferences' => ['admin' => ['users_sort_by' => UserSort::Email->value]],
    ]);

    $response = $this->actingAs($user)->getJson('/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.preferences', config('user.preferences.app'));
});

test('unauthenticated user cannot view profile', function () {
    $response = $this->getJson('/profile');

    $response->assertStatus(401);
});


