<?php

uses()->group('admin.mcp.user-tag.delete');

use App\Enums\UserRole;
use App\Mcp\Servers\AdminServer;
use App\Mcp\Tools\Admin\UserTag\DeleteUserTagTool;
use App\Models\Tag;
use App\Models\User;

test('admin can delete a user tag', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $tag = Tag::factory()->for($target)->create();

    $response = AdminServer::actingAs($admin)->tool(DeleteUserTagTool::class, [
        'user' => $target->id,
        'tag' => $tag->id,
    ]);

    $response->assertOk()
        ->assertSee('Done.');

    $this->assertModelMissing($tag);
});

test('cannot delete a tag belonging to a different user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $tag = Tag::factory()->create();

    $response = AdminServer::actingAs($admin)->tool(DeleteUserTagTool::class, [
        'user' => $target->id,
        'tag' => $tag->id,
    ]);

    $response->assertHasErrors(["No query results for tag id {$tag->id}."]);

    $this->assertModelExists($tag);
});
