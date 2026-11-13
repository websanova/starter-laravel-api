<?php

uses()->group('admin.mcp.user-bookmark.list');

use App\Enums\UserRole;
use App\Mcp\Servers\AdminServer;
use App\Mcp\Tools\Admin\UserBookmark\ListUserBookmarksTool;
use App\Models\Bookmark;
use App\Models\User;

test('admin can list only the given user bookmarks', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    Bookmark::factory()->for($target)->create(['title' => 'Laravel docs']);
    Bookmark::factory()->create(['title' => 'Other user docs']);

    $response = AdminServer::actingAs($admin)->tool(ListUserBookmarksTool::class, [
        'user' => $target->id,
    ]);

    $response->assertOk()
        ->assertSee('Laravel docs')
        ->assertDontSee('Other user docs');
});
