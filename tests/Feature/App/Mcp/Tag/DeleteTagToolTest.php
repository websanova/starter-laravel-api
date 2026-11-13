<?php

uses()->group('app.mcp.tag.delete');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Tag\DeleteTagTool;
use App\Models\Tag;
use App\Models\User;

test('user can delete their tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();

    $response = AppServer::actingAs($user)->tool(DeleteTagTool::class, [
        'tag' => $tag->id,
    ]);

    $response->assertOk()
        ->assertSee('Done.');

    $this->assertModelMissing($tag);
});

test('user cannot delete another user tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $response = AppServer::actingAs($user)->tool(DeleteTagTool::class, [
        'tag' => $tag->id,
    ]);

    $response->assertHasErrors(['You do not have permission to access this resource.']);

    $this->assertModelExists($tag);
});
