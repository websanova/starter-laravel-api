<?php

uses()->group('app.mcp.bookmark.update');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Bookmark\UpdateBookmarkTool;
use App\Models\Bookmark;
use App\Models\User;

test('user can update their bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->for($user)->create(['title' => 'Old title']);

    $response = AppServer::actingAs($user)->tool(UpdateBookmarkTool::class, [
        'bookmark' => $bookmark->id,
        'title' => 'New title',
    ]);

    $response->assertOk()
        ->assertSee('New title');

    $this->assertDatabaseHas('bookmarks', [
        'id' => $bookmark->id,
        'title' => 'New title',
    ]);
});

test('duplicate url returns a validation error', function () {
    $user = User::factory()->create();
    Bookmark::factory()->for($user)->create(['url' => 'https://laravel.com']);
    $bookmark = Bookmark::factory()->for($user)->create(['url' => 'https://pestphp.com']);

    $response = AppServer::actingAs($user)->tool(UpdateBookmarkTool::class, [
        'bookmark' => $bookmark->id,
        'url' => 'https://laravel.com',
    ]);

    $response->assertHasErrors(['You have already saved this URL.']);

    $this->assertDatabaseHas('bookmarks', [
        'id' => $bookmark->id,
        'url' => 'https://pestphp.com',
    ]);
});

test('user cannot update another user bookmark', function () {
    $user = User::factory()->create();
    $bookmark = Bookmark::factory()->create(['title' => 'Old title']);

    $response = AppServer::actingAs($user)->tool(UpdateBookmarkTool::class, [
        'bookmark' => $bookmark->id,
        'title' => 'New title',
    ]);

    $response->assertHasErrors(['You do not have permission to access this resource.']);

    $this->assertDatabaseHas('bookmarks', [
        'id' => $bookmark->id,
        'title' => 'Old title',
    ]);
});
