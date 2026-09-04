<?php

uses()->group('admin.user-subscription.destroy');

use App\Enums\UserRole;
use App\Models\User;

test('cancelling a user without a subscription is refused', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/subscription");

    $response->assertStatus(409)
        ->assertJsonPath('error', 'nothing_to_cancel');
});
