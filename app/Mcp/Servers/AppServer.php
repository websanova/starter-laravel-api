<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\App\CreateBookmarkTool;
use App\Mcp\Tools\App\DeleteBookmarkTool;
use App\Mcp\Tools\App\ListBookmarksTool;
use App\Mcp\Tools\App\UpdateBookmarkTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('App Server')]
#[Version('0.0.1')]
#[Instructions('Tools for the authenticated user to manage their own account data.')]
class AppServer extends Server
{
    protected array $tools = [
        CreateBookmarkTool::class,
        DeleteBookmarkTool::class,
        ListBookmarksTool::class,
        UpdateBookmarkTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
