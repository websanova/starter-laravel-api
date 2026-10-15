<?php

uses()->group('admin.user-oauth-token.destroy');

use App\Enums\UserRole;
use App\Models\User;
use Laravel\Passport\RefreshToken;

test('admin can revoke a user\'s oauth token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $token = oauthToken($target);
    $remaining = oauthToken($target);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(204);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $token->id, 'revoked' => true]);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $remaining->id, 'revoked' => false]);
});

test('revoking an oauth token also revokes its refresh token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $token = oauthToken($target);
    $refreshToken = RefreshToken::create([
        'id' => 'refresh-token',
        'access_token_id' => $token->id,
        'revoked' => false,
        'expires_at' => now()->addDays(30),
    ]);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(204);
    $this->assertDatabaseHas('oauth_refresh_tokens', ['id' => $refreshToken->id, 'revoked' => true]);
});

test('super can revoke a user\'s oauth token', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    $token = oauthToken($target);

    $response = $this->actingAs($super)->deleteJson("/admin/users/{$target->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(204);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $token->id, 'revoked' => true]);
});

test('admin can revoke another admin\'s oauth token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->assignRole(UserRole::Admin);
    $token = oauthToken($target);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(204);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $token->id, 'revoked' => true]);
});

test('admin cannot revoke a super user\'s oauth token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);
    $token = oauthToken($super);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$super->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $token->id, 'revoked' => false]);
});

test('regular user cannot revoke a user\'s oauth token', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $token = oauthToken($target);

    $response = $this->actingAs($user)->deleteJson("/admin/users/{$target->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $token->id, 'revoked' => false]);
});

test('unauthenticated user cannot revoke a user\'s oauth token', function () {
    $target = User::factory()->create();
    $token = oauthToken($target);

    $response = $this->deleteJson("/admin/users/{$target->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(401);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $token->id, 'revoked' => false]);
});

test('cannot revoke an oauth token belonging to a different user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();
    $token = oauthToken($other);

    $response = $this->actingAs($admin)->deleteJson("/admin/users/{$target->id}/oauth-tokens/{$token->id}");

    $response->assertStatus(404);
    $this->assertDatabaseHas('oauth_access_tokens', ['id' => $token->id, 'revoked' => false]);
});
