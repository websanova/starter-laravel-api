<?php

uses()->group('admin.user-subscription.show');

use App\Enums\UserRole;
use App\Models\User;

test('super can view a user subscription', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}/subscription");

    $response->assertStatus(200)
        ->assertJsonPath('data', null);
});

test('admin can view a user subscription', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/subscription");

    $response->assertStatus(200);
});

test('admin cannot view a super user subscription', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$super->id}/subscription");

    $response->assertStatus(403);
});

test('regular user cannot view a user subscription', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/users/{$target->id}/subscription");

    $response->assertStatus(403);
});

test('unauthenticated user cannot view a user subscription', function () {
    $target = User::factory()->create();

    $response = $this->getJson("/admin/users/{$target->id}/subscription");

    $response->assertStatus(401);
});
