<?php

uses()->group('app.mcp.profile.show');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Profile\ShowProfileTool;
use App\Models\User;

test('user can view their profile', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $response = AppServer::actingAs($user)->tool(ShowProfileTool::class);

    $response->assertOk()
        ->assertSee('jane@example.com');
});
