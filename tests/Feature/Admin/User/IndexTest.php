<?php

uses()->group('admin.user.index');

use App\Enums\Role;
use App\Models\User;

test('super can list users', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::Super);

    User::factory()->count(3)->create();

    $response = $this->actingAs($super)->getJson('/admin/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'first_name', 'last_name', 'email', 'role']],
            'meta',
            'links',
        ]);
});

test('admin can list users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users');

    $response->assertStatus(200);
});

test('regular user cannot list users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/admin/users');

    $response->assertStatus(403);
});

test('unauthenticated user cannot list users', function () {
    $response = $this->getJson('/admin/users');

    $response->assertStatus(401);
});

test('can search users by name', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    User::factory()->create(['first_name' => 'Findme']);
    User::factory()->create(['first_name' => 'Other']);

    $response = $this->actingAs($admin)->getJson('/admin/users?search=Findme');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.first_name', 'Findme');
});

test('can search users by email', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    User::factory()->create(['email' => 'target@example.com']);
    User::factory()->create(['email' => 'other@example.com']);

    $response = $this->actingAs($admin)->getJson('/admin/users?search=target@example.com');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can filter users by role', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(Role::Admin);

    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users?role=admin');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('can filter trashed users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    $deleted = User::factory()->create();
    $deleted->delete();

    $response = $this->actingAs($admin)->getJson('/admin/users?trashed=only');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can set per page limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::Admin);

    User::factory()->count(5)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users?per_page=2');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});
