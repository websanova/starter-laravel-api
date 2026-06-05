<?php

uses()->group('admin.user-force-delete.destroy');

use App\Enums\AccountPruneStrategy;
use App\Enums\UserRole;
use App\Models\User;

test('admin can force delete a regular user with delete strategy', function () {
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Delete]);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/force");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('admin can force delete a regular user with anonymize strategy', function () {
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Anonymize]);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['email' => 'original@example.com']);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/force");

    $response->assertStatus(204);
    $this->assertDatabaseHas('users', ['id' => $target->id]);
    expect($target->fresh()->email)->not->toBe('original@example.com');
});

test('admin can force delete a soft-deleted user', function () {
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Delete]);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->delete();

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/force");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('admin cannot force delete another admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$otherAdmin->id}/force");

    $response->assertStatus(403);
});

test('admin cannot force delete a super user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$super->id}/force");

    $response->assertStatus(403);
});

test('super can force delete an admin', function () {
    config(['auth.delete.prune_strategy' => AccountPruneStrategy::Delete]);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$admin->id}/force");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('users', ['id' => $admin->id]);
});

test('regular user cannot force delete another user', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/force");

    $response->assertStatus(403);
});

test('unauthenticated user cannot force delete a user', function () {
    $target = User::factory()->create();

    $response = $this->deleteJson("/admin/users/{$target->id}/force");

    $response->assertStatus(401);
});
