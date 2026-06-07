<?php

uses()->group('admin.plan.store');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('super can create a plan', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($super)->postJson('/admin/plans', [
        'name' => 'Enterprise',
        'slug' => 'enterprise',
        'monthly_price' => 4999,
        'yearly_price' => 49990,
        'features' => ['bookmarks' => null, 'categories' => null],
        'is_active' => true,
        'sort_order' => 3,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Enterprise')
        ->assertJsonPath('data.slug', 'enterprise')
        ->assertJsonPath('data.monthly_price', 4999)
        ->assertJsonPath('data.yearly_price', 49990)
        ->assertJsonPath('data.features.bookmarks', null)
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('plans', ['slug' => 'enterprise']);
});

test('admin can create a plan', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(201);
});

test('regular user cannot create a plan', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot create a plan', function () {
    $response = $this->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(401);
});

test('name is required', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'slug' => 'basic',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('slug is required', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'name' => 'Basic',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('slug');
});

test('slug must be unique', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->create(['slug' => 'basic']);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'name' => 'Basic',
        'slug' => 'basic',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('slug');
});

test('can create a plan with stripe price ids', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/plans', [
        'name' => 'Business',
        'slug' => 'business',
        'stripe_monthly_price_id' => 'price_monthly_abc123',
        'stripe_yearly_price_id' => 'price_yearly_abc123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.stripe_monthly_price_id', 'price_monthly_abc123')
        ->assertJsonPath('data.stripe_yearly_price_id', 'price_yearly_abc123');
});
