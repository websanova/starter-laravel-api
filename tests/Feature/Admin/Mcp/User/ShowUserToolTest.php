<?php

uses()->group('admin.mcp.user.show');

use App\Enums\UserRole;
use App\Mcp\Servers\AdminServer;
use App\Mcp\Tools\Admin\User\ShowUserTool;
use App\Models\User;

test('admin can view a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['email' => 'jane@example.com']);

    $response = AdminServer::actingAs($admin)->tool(ShowUserTool::class, [
        'user' => $target->id,
    ]);

    $response->assertOk()
        ->assertSee('jane@example.com');
});
