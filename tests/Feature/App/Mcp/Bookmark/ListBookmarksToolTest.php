<?php

uses()->group('app.mcp.bookmark.list');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Bookmark\ListBookmarksTool;
use App\Models\Bookmark;
use App\Models\User;

test('user can list only their own bookmarks', function () {
    $user = User::factory()->create();
    Bookmark::factory()->for($user)->create(['title' => 'Laravel docs']);
    Bookmark::factory()->create(['title' => 'Other user docs']);

    $response = AppServer::actingAs($user)->tool(ListBookmarksTool::class);

    $response->assertOk()
        ->assertSee('Laravel docs')
        ->assertDontSee('Other user docs');
});
