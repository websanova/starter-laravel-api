<?php

uses()->group('app.mcp.bookmark.create');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Bookmark\CreateBookmarkTool;
use App\Models\Bookmark;
use App\Models\User;

test('user can create a bookmark', function () {
    $user = User::factory()->create();

    $response = AppServer::actingAs($user)->tool(CreateBookmarkTool::class, [
        'url' => 'https://laravel.com',
        'title' => 'Laravel docs',
    ]);

    $response->assertOk()
        ->assertSee('Laravel docs');

    $this->assertDatabaseHas('bookmarks', [
        'user_id' => $user->id,
        'title' => 'Laravel docs',
    ]);
});

test('duplicate url returns a validation error', function () {
    $user = User::factory()->create();
    Bookmark::factory()->for($user)->create(['url' => 'https://laravel.com']);

    $response = AppServer::actingAs($user)->tool(CreateBookmarkTool::class, [
        'url' => 'https://laravel.com',
        'title' => 'Laravel docs',
    ]);

    $response->assertHasErrors(['You have already saved this URL.']);

    $this->assertDatabaseCount('bookmarks', 1);
});
