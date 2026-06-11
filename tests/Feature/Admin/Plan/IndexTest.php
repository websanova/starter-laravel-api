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
            'data' => [['id', 'name', 'slug', 'prices', 'features', 'is_active', 'sort_order']],
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
        ->assertJsonCount(4, 'data');
});

test('plans are sorted by sort_order ascending by default', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->create(['name' => 'Alpha', 'sort_order' => 10]);
    Plan::factory()->create(['name' => 'Gamma', 'sort_order' => 30]);
    Plan::factory()->create(['name' => 'Beta', 'sort_order' => 20]);

    $response = $this->actingAs($admin)->getJson('/admin/plans');

    $response->assertStatus(200);

    $names = collect($response->json('data'))->pluck('name');
    $alphaIndex = $names->search('Alpha');
    $betaIndex = $names->search('Beta');
    $gammaIndex = $names->search('Gamma');

    expect($alphaIndex)->toBeLessThan($betaIndex);
    expect($betaIndex)->toBeLessThan($gammaIndex);
});

test('plans can be sorted by name', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    Plan::factory()->create(['name' => 'Zeta']);
    Plan::factory()->create(['name' => 'Alpha']);

    $response = $this->actingAs($admin)->getJson('/admin/plans?sort_by=name&sort_dir=asc');

    $response->assertStatus(200);

    $names = collect($response->json('data'))->pluck('name')->values();
    $alphaIndex = $names->search('Alpha');
    $zetaIndex = $names->search('Zeta');

    expect($alphaIndex)->toBeLessThan($zetaIndex);
});

test('invalid sort_by value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->getJson('/admin/plans?sort_by=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('sort_by');
});
