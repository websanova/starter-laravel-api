<?php

uses()->group('admin.plan.price.update');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\Price;
use App\Models\User;

test('unauthenticated user cannot update a price', function () {
    $plan = Plan::factory()->create();
    $price = Price::factory()->for($plan)->create();

    $response = $this->patchJson("/admin/plans/{$plan->id}/prices/{$price->id}", [
        'stripe_product_id' => 'prod_abc123',
    ]);

    $response->assertStatus(401);
});

test('regular user cannot update a price', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $price = Price::factory()->for($plan)->create();

    $response = $this->actingAs($user)->patchJson("/admin/plans/{$plan->id}/prices/{$price->id}", [
        'stripe_product_id' => 'prod_abc123',
    ]);

    $response->assertStatus(403);
});

test('stripe_product_id is required', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);
    $plan = Plan::factory()->create();
    $price = Price::factory()->for($plan)->create();

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}/prices/{$price->id}", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('stripe_product_id');
});

test('price must belong to the plan in the route', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);
    $plan = Plan::factory()->create();
    $otherPlan = Plan::factory()->create();
    $price = Price::factory()->for($otherPlan)->create();

    $response = $this->actingAs($admin)->patchJson("/admin/plans/{$plan->id}/prices/{$price->id}", [
        'stripe_product_id' => 'prod_abc123',
    ]);

    $response->assertStatus(404);
});
