<?php

uses()->group('admin.mcp.hello');

use App\Enums\UserRole;
use App\Models\User;

test('admin can call the hello tool', function () {
    $admin = User::factory()->create(['first_name' => 'Jane']);
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($admin)->postJson('/admin/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'hello-tool', 'arguments' => []],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.content.0.text', "Hello admin Jane, you are user #{$admin->id}.");
});

test('regular user cannot call the admin hello tool', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/admin/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'hello-tool', 'arguments' => []],
    ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot call the admin hello tool', function () {
    $response = $this->postJson('/admin/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'hello-tool', 'arguments' => []],
    ]);

    $response->assertUnauthorized();
});
