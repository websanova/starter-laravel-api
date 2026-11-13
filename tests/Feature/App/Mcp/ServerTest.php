<?php

uses()->group('app.mcp.server');

use App\Models\OAuthUser;
use App\Models\User;
use Laravel\Passport\Passport;

test('user can call an app tool', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    Passport::actingAs(OAuthUser::find($user->id));

    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'show-profile-tool', 'arguments' => []],
    ]);

    $response->assertOk()
        ->assertSee('jane@example.com');
});

test('unauthenticated user cannot call an app tool', function () {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'show-profile-tool', 'arguments' => []],
    ]);

    $response->assertUnauthorized();
});
