<?php

uses()->group('admin.user-subscription.update');

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;

test('regular user cannot update a user subscription', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->putJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(403);
});

test('unauthenticated user cannot update a user subscription', function () {
    $target = User::factory()->create();

    $response = $this->putJson("/admin/users/{$target->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(401);
});

test('admin cannot update a super user subscription', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->putJson("/admin/users/{$super->id}/subscription", [
        'plan' => 'pro',
        'interval' => 'monthly',
    ]);

    $response->assertStatus(403);
});

test('admin can assign complimentary plan without interval', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $plan = Plan::factory()->complimentary()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($admin)->putJson("/admin/users/{$target->id}/subscription", [
        'plan' => $plan->slug,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', __('responses.admin.user.subscription_updated'));

    expect($target->fresh()->plan_id)->toBe($plan->id);
});
