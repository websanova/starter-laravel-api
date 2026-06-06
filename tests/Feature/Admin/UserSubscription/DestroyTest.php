<?php

uses()->group('admin.user-subscription.destroy');

use App\Enums\UserRole;
use App\Models\User;

test('regular user cannot cancel a user subscription', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/subscription");

    $response->assertStatus(403);
});

test('unauthenticated user cannot cancel a user subscription', function () {
    $target = User::factory()->create();

    $response = $this->deleteJson("/admin/users/{$target->id}/subscription");

    $response->assertStatus(401);
});

test('admin cannot cancel a super user subscription', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$super->id}/subscription");

    $response->assertStatus(403);
});
