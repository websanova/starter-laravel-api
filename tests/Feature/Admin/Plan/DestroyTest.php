<?php

uses()->group('admin.plan.destroy');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('super can delete a plan with no users', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $plan = Plan::factory()->create();

    $response = $this->actingAs($super)->deleteJson("/admin/plans/{$plan->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
});

test('admin can delete a plan with no users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/admin/plans/{$plan->id}");

    $response->assertStatus(204);
});

test('admin cannot delete a plan that has users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->create();
    User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($admin)->deleteJson("/admin/plans/{$plan->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('plans', ['id' => $plan->id]);
});

test('super cannot delete a plan that has users', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $plan = Plan::factory()->create();
    User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($super)->deleteJson("/admin/plans/{$plan->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('plans', ['id' => $plan->id]);
});

test('regular user cannot delete a plan', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/admin/plans/{$plan->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot delete a plan', function () {
    $plan = Plan::factory()->create();

    $response = $this->deleteJson("/admin/plans/{$plan->id}");

    $response->assertStatus(401);
});
