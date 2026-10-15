<?php

uses()->group('admin.user-sanctum-token.index');

use App\Enums\UserRole;
use App\Models\User;

test('super can list a user\'s sanctum tokens', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    $target->createToken('auth');
    $target->createToken('auth');

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}/sanctum-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at']],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('admin can list a user\'s sanctum tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->createToken('auth');

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/sanctum-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('admin cannot list a super user\'s sanctum tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$super->id}/sanctum-tokens");

    $response->assertStatus(403);
});

test('regular user cannot list a user\'s sanctum tokens', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/users/{$target->id}/sanctum-tokens");

    $response->assertStatus(403);
});

test('unauthenticated user cannot list a user\'s sanctum tokens', function () {
    $target = User::factory()->create();

    $response = $this->getJson("/admin/users/{$target->id}/sanctum-tokens");

    $response->assertStatus(401);
});

test('returns only the target user\'s sanctum tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();

    $token = $target->createToken('target');
    $other->createToken('other');

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/sanctum-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $token->accessToken->id);
});

test('does not return expired sanctum tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $active = $target->createToken('active', ['*'], now()->addDay());
    $target->createToken('expired', ['*'], now()->subMinute());

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/sanctum-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $active->accessToken->id);
});

test('sanctum tokens are sorted by created_at descending', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->createToken('old')->accessToken->forceFill(['created_at' => now()->subDays(2)])->save();
    $target->createToken('new');
    $target->createToken('mid')->accessToken->forceFill(['created_at' => now()->subDay()])->save();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/sanctum-tokens");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.name', 'new')
        ->assertJsonPath('data.1.name', 'mid')
        ->assertJsonPath('data.2.name', 'old');
});

test('can set per page limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->createToken('auth');
    $target->createToken('auth');
    $target->createToken('auth');

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/sanctum-tokens?per_page=2");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 3);
});

test('invalid per_page value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/sanctum-tokens?per_page=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('per_page');
});
