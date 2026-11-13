<?php

uses()->group('app.mcp.tag.update');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Tag\UpdateTagTool;
use App\Models\Tag;
use App\Models\User;

test('user can rename their tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'Old name']);

    $response = AppServer::actingAs($user)->tool(UpdateTagTool::class, [
        'tag' => $tag->id,
        'name' => 'New name',
    ]);

    $response->assertOk()
        ->assertSee('New name');

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'New name',
    ]);
});

test('duplicate name returns a validation error', function () {
    $user = User::factory()->create();
    Tag::factory()->for($user)->create(['name' => 'Laravel']);
    $tag = Tag::factory()->for($user)->create(['name' => 'Vue']);

    $response = AppServer::actingAs($user)->tool(UpdateTagTool::class, [
        'tag' => $tag->id,
        'name' => 'Laravel',
    ]);

    $response->assertHasErrors(['You already have a tag with this name.']);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Vue',
    ]);
});

test('user cannot rename another user tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['name' => 'Old name']);

    $response = AppServer::actingAs($user)->tool(UpdateTagTool::class, [
        'tag' => $tag->id,
        'name' => 'New name',
    ]);

    $response->assertHasErrors(['You do not have permission to access this resource.']);

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Old name',
    ]);
});
