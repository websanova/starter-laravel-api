<?php

uses()->group('account.category.index');

use App\Models\Category;
use App\Models\User;

test('user can list their categories', function () {
    $user = User::factory()->create();
    Category::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/account/categories');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'name', 'created_at', 'updated_at']],
        ]);
});

test('user only sees their own categories', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Category::factory()->count(2)->create(['user_id' => $user->id]);
    Category::factory()->count(3)->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->getJson('/account/categories');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('unauthenticated user cannot list categories', function () {
    $response = $this->getJson('/account/categories');

    $response->assertStatus(401);
});
