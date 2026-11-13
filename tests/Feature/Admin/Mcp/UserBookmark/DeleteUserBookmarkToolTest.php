<?php

uses()->group('admin.mcp.user-bookmark.delete');

use App\Enums\UserRole;
use App\Mcp\Servers\AdminServer;
use App\Mcp\Tools\Admin\UserBookmark\DeleteUserBookmarkTool;
use App\Models\Bookmark;
use App\Models\User;

test('admin can delete a user bookmark', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $bookmark = Bookmark::factory()->for($target)->create();

    $response = AdminServer::actingAs($admin)->tool(DeleteUserBookmarkTool::class, [
        'user' => $target->id,
        'bookmark' => $bookmark->id,
    ]);

    $response->assertOk()
        ->assertSee('Done.');

    $this->assertModelMissing($bookmark);
});

test('cannot delete a bookmark belonging to a different user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $bookmark = Bookmark::factory()->create();

    $response = AdminServer::actingAs($admin)->tool(DeleteUserBookmarkTool::class, [
        'user' => $target->id,
        'bookmark' => $bookmark->id,
    ]);

    $response->assertHasErrors(["No query results for bookmark id {$bookmark->id}."]);

    $this->assertModelExists($bookmark);
});
