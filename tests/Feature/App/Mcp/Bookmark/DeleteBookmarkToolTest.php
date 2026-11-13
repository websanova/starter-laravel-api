<?php

uses()->group('app.mcp.bookmark.delete');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Bookmark\DeleteBookmarkTool;
use App\Models\Bookmark;
use App\Models\User;

test('user can delete their bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->for($user)->create();

    $response = AppServer::actingAs($user)->tool(DeleteBookmarkTool::class, [
        'bookmark' => $bookmark->id,
    ]);

    $response->assertOk()
        ->assertSee('Done.');

    $this->assertModelMissing($bookmark);
});

test('user cannot delete another user bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create();

    $response = AppServer::actingAs($user)->tool(DeleteBookmarkTool::class, [
        'bookmark' => $bookmark->id,
    ]);

    $response->assertHasErrors(['You do not have permission to access this resource.']);

    $this->assertModelExists($bookmark);
});
