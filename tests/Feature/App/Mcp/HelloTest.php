<?php

uses()->group('app.mcp.hello');

use App\Models\User;

test('user can call the hello tool', function () {
    $user = User::factory()->create(['first_name' => 'Jane']);

    $response = $this->actingAs($user)->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'hello-tool', 'arguments' => []],
    ]);

    $response->assertOk()
        ->assertJsonPath('result.content.0.text', "Hello Jane, you are user #{$user->id}.");
});

test('unauthenticated user cannot call the hello tool', function () {
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => 'hello-tool', 'arguments' => []],
    ]);

    $response->assertUnauthorized();
});
