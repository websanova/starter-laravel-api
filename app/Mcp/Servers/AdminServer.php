<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Admin\HelloTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Admin Server')]
#[Version('0.0.1')]
#[Instructions('Tools for administrators to manage users and application data.')]
class AdminServer extends Server
{
    protected array $tools = [
        HelloTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
