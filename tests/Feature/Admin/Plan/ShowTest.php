<?php

uses()->group('admin.plan.show');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('super can view a plan', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $plan = Plan::factory()->create();

    $response = $this->actingAs($super)->getJson("/admin/plans/{$plan->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'slug', 'prices', 'features', 'is_active', 'sort_order', 'created_at', 'updated_at'],
        ]);
});

test('admin can view a plan', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/plans/{$plan->id}");

    $response->assertStatus(200);
});

test('regular user cannot view a plan', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/plans/{$plan->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot view a plan', function () {
    $plan = Plan::factory()->create();

    $response = $this->getJson("/admin/plans/{$plan->id}");

    $response->assertStatus(401);
});
