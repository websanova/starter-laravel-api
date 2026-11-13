<?php

uses()->group('admin.mcp.user-tag.list');

use App\Enums\UserRole;
use App\Mcp\Servers\AdminServer;
use App\Mcp\Tools\Admin\UserTag\ListUserTagsTool;
use App\Models\Tag;
use App\Models\User;

test('admin can list only the given user tags', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Tag::factory()->for($target)->create(['name' => 'Laravel']);
    Tag::factory()->create(['name' => 'Vue']);

    $response = AdminServer::actingAs($admin)->tool(ListUserTagsTool::class, [
        'user' => $target->id,
    ]);

    $response->assertOk()
        ->assertSee('Laravel')
        ->assertDontSee('Vue');
});
