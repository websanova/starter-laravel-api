<?php

uses()->group('admin.mcp.user.update');

use App\Enums\UserRole;
use App\Mcp\Servers\AdminServer;
use App\Mcp\Tools\Admin\User\UpdateUserTool;
use App\Models\User;

test('admin can update a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['first_name' => 'Old']);

    $response = AdminServer::actingAs($admin)->tool(UpdateUserTool::class, [
        'user' => $target->id,
        'first_name' => 'Jane',
    ]);

    $response->assertOk()
        ->assertSee('"first_name":"Jane"');

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'first_name' => 'Jane',
    ]);
});

test('invalid timezone returns a validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create(['timezone' => 'Europe/London']);

    $response = AdminServer::actingAs($admin)->tool(UpdateUserTool::class, [
        'user' => $target->id,
        'timezone' => 'Not/AZone',
    ]);

    $response->assertHasErrors(['The selected timezone is invalid.']);

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'timezone' => 'Europe/London',
    ]);
});
