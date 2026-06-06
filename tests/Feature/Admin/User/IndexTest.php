<?php

uses()->group('admin.user.index');

use App\Enums\UserRole;
use App\Models\User;

test('super can list users', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

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
    $admin->assignRole(UserRole::Admin);

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
    $admin->assignRole(UserRole::Admin);

    User::factory()->create(['first_name' => 'Findme']);
    User::factory()->create(['first_name' => 'Other']);

    $response = $this->actingAs($admin)->getJson('/admin/users?search=Findme');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.first_name', 'Findme');
});

test('can search users by email', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    User::factory()->create(['email' => 'target@example.com']);
    User::factory()->create(['email' => 'other@example.com']);

    $response = $this->actingAs($admin)->getJson('/admin/users?search=target@example.com');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can filter users by single role', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(UserRole::Admin);

    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users?role=admin');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('can filter users by multiple roles with array syntax', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(UserRole::Admin);

    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users?role[]=admin&role[]=super');

    // 2 admins + 1 seeded super
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can filter users by multiple roles with comma-separated syntax', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(UserRole::Admin);

    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users?role=admin,super');

    // 2 admins + 1 seeded super
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can filter users by super role', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    User::factory()->count(2)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users?role=super');

    // 1 seeded super
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can filter trashed users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $deleted = User::factory()->create();
    $deleted->delete();

    $response = $this->actingAs($admin)->getJson('/admin/users?trashed=only');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('can set per page limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    User::factory()->count(5)->create();

    $response = $this->actingAs($admin)->getJson('/admin/users?per_page=2');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('users are sorted by created_at descending by default', function () {
    $admin = User::factory()->create(['first_name' => 'Admin', 'created_at' => now()->addDays(10)]);
    $admin->assignRole(UserRole::Admin);

    $old = User::factory()->create(['first_name' => 'Old', 'created_at' => now()->addDays(11)]);
    $new = User::factory()->create(['first_name' => 'New', 'created_at' => now()->addDays(13)]);
    $mid = User::factory()->create(['first_name' => 'Mid', 'created_at' => now()->addDays(12)]);

    $response = $this->actingAs($admin)->getJson('/admin/users');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.first_name', 'New')
        ->assertJsonPath('data.1.first_name', 'Mid')
        ->assertJsonPath('data.2.first_name', 'Old');
});

test('users can be sorted by name ascending', function () {
    $admin = User::factory()->create(['first_name' => 'Admin']);
    $admin->assignRole(UserRole::Admin);

    User::factory()->create(['first_name' => 'Zebra', 'last_name' => 'Smith']);
    User::factory()->create(['first_name' => 'Apple', 'last_name' => 'Jones']);

    $response = $this->actingAs($admin)->getJson('/admin/users?sort_by=name&sort_dir=asc');

    // Seeded super user (first_name: 'Super') also present in results
    $response->assertStatus(200)
        ->assertJsonPath('data.0.first_name', 'Admin')
        ->assertJsonPath('data.1.first_name', 'Apple')
        ->assertJsonPath('data.2.first_name', 'Super')
        ->assertJsonPath('data.3.first_name', 'Zebra');
});

test('users can be sorted by email', function () {
    $admin = User::factory()->create(['email' => 'b@example.com']);
    $admin->assignRole(UserRole::Admin);

    User::factory()->create(['email' => 'a@example.com']);
    User::factory()->create(['email' => 'c@example.com']);

    $response = $this->actingAs($admin)->getJson('/admin/users?sort_by=email&sort_dir=asc');

    // Seeded super user (super@starter.com) also present in results
    $response->assertStatus(200)
        ->assertJsonPath('data.0.email', 'a@example.com')
        ->assertJsonPath('data.1.email', 'b@example.com')
        ->assertJsonPath('data.2.email', 'c@example.com')
        ->assertJsonPath('data.3.email', 'super@starter.com');
});

test('users can be sorted by last_active_at', function () {
    $admin = User::factory()->create(['last_active_at' => now()->subDays(3)]);
    $admin->assignRole(UserRole::Admin);

    $recent = User::factory()->create(['first_name' => 'Recent', 'last_active_at' => now()->addDay()]);
    $stale = User::factory()->create(['first_name' => 'Stale', 'last_active_at' => now()->subWeek()]);

    $response = $this->actingAs($admin)->getJson('/admin/users?sort_by=last_active_at&sort_dir=desc');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.first_name', 'Recent');
});

test('invalid sort_by value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->getJson('/admin/users?sort_by=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_by');
});

test('invalid sort_dir value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->getJson('/admin/users?sort_dir=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_dir');
});
