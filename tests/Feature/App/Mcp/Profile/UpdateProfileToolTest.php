<?php

uses()->group('app.mcp.profile.update');

use App\Mcp\Servers\AppServer;
use App\Mcp\Tools\App\Profile\UpdateProfileTool;
use App\Models\User;

test('user can update their profile', function () {
    $user = User::factory()->create(['first_name' => 'Old']);

    $response = AppServer::actingAs($user)->tool(UpdateProfileTool::class, [
        'first_name' => 'Jane',
    ]);

    $response->assertOk()
        ->assertSee('"first_name":"Jane"');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'Jane',
    ]);
});

test('invalid timezone returns a validation error', function () {
    $user = User::factory()->create(['timezone' => 'Europe/London']);

    $response = AppServer::actingAs($user)->tool(UpdateProfileTool::class, [
        'timezone' => 'Not/AZone',
    ]);

    $response->assertHasErrors(['The selected timezone is invalid.']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'timezone' => 'Europe/London',
    ]);
});
