<?php

uses()->group('account.tag.index');

use App\Models\Tag;
use App\Models\User;

test('user can list their tags', function () {
    $user = User::factory()->create();

    Tag::factory()->for($user)->create(['name' => 'vue']);
    Tag::factory()->for($user)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->getJson('/account/tags');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'laravel')
        ->assertJsonPath('data.1.name', 'vue');
});

test('user cannot see other users tags', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Tag::factory()->for($other)->create(['name' => 'laravel']);

    $response = $this->actingAs($user)->getJson('/account/tags');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('unauthenticated user cannot list tags', function () {
    $response = $this->getJson('/account/tags');

    $response->assertStatus(401);
});
