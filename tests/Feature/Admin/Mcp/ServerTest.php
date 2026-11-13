<?php

uses()->group('admin.mcp.server');

use App\Enums\UserRole;
use App\Models\OAuthUser;
use App\Models\User;
use Laravel\Passport\Passport;

test('admin can call an admin tool', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['email' => 'jane@example.com']);

    Passport::actingAs(OAuthUser::find($admin->id));

    $response = $this->postJson('/admin/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'show-user-tool', 'arguments' => ['user' => $target->id]],
    ]);

    $response->assertOk()
        ->assertSee('jane@example.com');
});

test('regular user cannot call an admin tool', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    Passport::actingAs(OAuthUser::find($user->id));

    $response = $this->postJson('/admin/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'show-user-tool', 'arguments' => ['user' => $target->id]],
    ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot call an admin tool', function () {
    $target = User::factory()->create();

    $response = $this->postJson('/admin/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'show-user-tool', 'arguments' => ['user' => $target->id]],
    ]);

    $response->assertUnauthorized();
});
