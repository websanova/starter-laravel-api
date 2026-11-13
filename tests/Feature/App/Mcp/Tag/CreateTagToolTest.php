<?php

uses()->group('app.mcp.tag.create');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Tag\CreateTagTool;
use App\Models\Tag;
use App\Models\User;

test('user can create a tag', function () {
    $user = User::factory()->create();

    $response = AppServer::actingAs($user)->tool(CreateTagTool::class, [
        'name' => 'Laravel',
    ]);

    $response->assertOk()
        ->assertSee('Laravel');

    $this->assertDatabaseHas('tags', [
        'user_id' => $user->id,
        'name' => 'Laravel',
    ]);
});

test('duplicate name returns a validation error', function () {
    $user = User::factory()->create();
    Tag::factory()->for($user)->create(['name' => 'laravel']);

    $response = AppServer::actingAs($user)->tool(CreateTagTool::class, [
        'name' => 'Laravel',
    ]);

    $response->assertHasErrors(['You already have a tag with this name.']);

    $this->assertDatabaseCount('tags', 1);
});
