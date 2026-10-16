<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Admin\HelloTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Admin Server')]
#[Version('0.0.1')]
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

    /**
     * Set the instructions from the lang file, attributes cannot call __().
     */
    protected function boot(): void
    {
        $this->instructions = __('mcp.admin.instructions');
    }
}
