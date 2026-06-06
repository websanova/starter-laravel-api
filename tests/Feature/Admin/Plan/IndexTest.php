<?php

uses()->group('admin.plan.index');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('super can list plans', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    Plan::factory()->count(3)->create();

    $response = $this->actingAs($super)->getJson('/admin/plans');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'name', 'slug', 'monthly_price', 'yearly_price', 'features', 'is_active', 'sort_order']],
            'meta',
            'links',
        ]);
});

test('admin can list plans', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->count(2)->create();

    $response = $this->actingAs($admin)->getJson('/admin/plans');

    $response->assertStatus(200);
});

test('regular user cannot list plans', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/admin/plans');

    $response->assertStatus(403);
});

test('unauthenticated user cannot list plans', function () {
    $response = $this->getJson('/admin/plans');

    $response->assertStatus(401);
});

test('can filter plans by active status', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->count(2)->create(['is_active' => true]);
    Plan::factory()->create(['is_active' => false]);

    $response = $this->actingAs($admin)->getJson('/admin/plans?active=1');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('plans are sorted by sort_order ascending by default', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->create(['name' => 'Pro', 'sort_order' => 2]);
    Plan::factory()->create(['name' => 'Free', 'sort_order' => 0]);
    Plan::factory()->create(['name' => 'Business', 'sort_order' => 1]);

    $response = $this->actingAs($admin)->getJson('/admin/plans');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'Free')
        ->assertJsonPath('data.1.name', 'Business')
        ->assertJsonPath('data.2.name', 'Pro');
});

test('plans can be sorted by name', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->create(['name' => 'Pro']);
    Plan::factory()->create(['name' => 'Basic']);

    $response = $this->actingAs($admin)->getJson('/admin/plans?sort_by=name&sort_dir=asc');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'Basic')
        ->assertJsonPath('data.1.name', 'Pro');
});

test('invalid sort_by value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->getJson('/admin/plans?sort_by=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_by');
});
