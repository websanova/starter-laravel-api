<?php

uses()->group('admin.user-oauth-token.index');

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\RefreshToken;

test('super can list a user\'s oauth tokens', function () {
    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $target = User::factory()->create();
    oauthToken($target);
    oauthToken($target);

    $response = $this->actingAs($super)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'client_id', 'client_name', 'scopes', 'expires_at', 'created_at']],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('admin can list a user\'s oauth tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    oauthToken($target);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('admin cannot list a super user\'s oauth tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$super->id}/oauth-tokens");

    $response->assertStatus(403);
});

test('regular user cannot list a user\'s oauth tokens', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(403);
});

test('unauthenticated user cannot list a user\'s oauth tokens', function () {
    $target = User::factory()->create();

    $response = $this->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(401);
});

test('returns the client name with each oauth token', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $client = Client::factory()->create(['name' => 'Claude']);
    oauthToken($target, ['client_id' => $client->id]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.client_id', $client->id)
        ->assertJsonPath('data.0.client_name', 'Claude');
});

test('returns only the target user\'s oauth tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $other = User::factory()->create();

    $token = oauthToken($target);
    oauthToken($other);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $token->id);
});

test('does not return revoked or expired oauth tokens', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $active = oauthToken($target);
    oauthToken($target, ['revoked' => true]);
    oauthToken($target, ['expires_at' => now()->subMinute()]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $active->id);
});

test('returns an expired oauth token while its refresh token is live', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $token = oauthToken($target, ['expires_at' => now()->subMinute()]);
    $refreshToken = RefreshToken::create([
        'id' => Str::random(80),
        'access_token_id' => $token->id,
        'revoked' => false,
        'expires_at' => now()->addDays(30),
    ]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $token->id)
        ->assertJsonPath('data.0.expires_at', $refreshToken->expires_at->toJSON());
});

test('does not return oauth tokens whose refresh token is revoked or expired', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $active = oauthToken($target);
    $revoked = oauthToken($target);
    $expired = oauthToken($target);

    RefreshToken::create([
        'id' => Str::random(80),
        'access_token_id' => $active->id,
        'revoked' => false,
        'expires_at' => now()->addDays(30),
    ]);
    RefreshToken::create([
        'id' => Str::random(80),
        'access_token_id' => $revoked->id,
        'revoked' => true,
        'expires_at' => now()->addDays(30),
    ]);
    RefreshToken::create([
        'id' => Str::random(80),
        'access_token_id' => $expired->id,
        'revoked' => false,
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $active->id);
});

test('oauth tokens are sorted by created_at descending', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $old = oauthToken($target, ['created_at' => now()->subDays(2)]);
    $new = oauthToken($target, ['created_at' => now()]);
    $mid = oauthToken($target, ['created_at' => now()->subDay()]);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $new->id)
        ->assertJsonPath('data.1.id', $mid->id)
        ->assertJsonPath('data.2.id', $old->id);
});

test('can set per page limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    oauthToken($target);
    oauthToken($target);
    oauthToken($target);

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens?per_page=2");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 3);
});

test('invalid per_page value returns validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();

    $response = $this->actingAs($admin)->getJson("/admin/users/{$target->id}/oauth-tokens?per_page=invalid");

    $response->assertStatus(422)
        ->assertJsonValidationErrors('per_page');
});
