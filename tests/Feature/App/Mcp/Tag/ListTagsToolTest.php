<?php

uses()->group('app.mcp.tag.list');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Tag\ListTagsTool;
use App\Models\Tag;
use App\Models\User;

test('user can list only their own tags', function () {
    $user = User::factory()->create();
    Tag::factory()->for($user)->create(['name' => 'Laravel']);
    Tag::factory()->create(['name' => 'Vue']);

    $response = AppServer::actingAs($user)->tool(ListTagsTool::class);

    $response->assertOk()
        ->assertSee('Laravel')
        ->assertDontSee('Vue');
});
