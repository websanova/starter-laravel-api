<?php

uses()->group('account.category.update');

use App\Models\Category;
use App\Models\User;

test('user can update their category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/account/categories/{$category->id}", [
        'name' => 'Updated',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated');
});

test('user cannot update another user category', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->putJson("/account/categories/{$category->id}", [
        'name' => 'Hijacked',
    ]);

    $response->assertStatus(403);
});

test('name must not exceed 255 characters', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/account/categories/{$category->id}", [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('unauthenticated user cannot update a category', function () {
    $category = Category::factory()->create();

    $response = $this->putJson("/account/categories/{$category->id}", [
        'name' => 'Updated',
    ]);

    $response->assertStatus(401);
});

