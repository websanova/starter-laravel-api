<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Admin\User\ShowUserTool;
use App\Mcp\Tools\Admin\User\UpdateUserTool;
use App\Mcp\Tools\Admin\UserBookmark\DeleteUserBookmarkTool;
use App\Mcp\Tools\Admin\UserBookmark\ListUserBookmarksTool;
use App\Mcp\Tools\Admin\UserTag\DeleteUserTagTool;
use App\Mcp\Tools\Admin\UserTag\ListUserTagsTool;
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
        DeleteUserBookmarkTool::class,
        DeleteUserTagTool::class,
        ListUserBookmarksTool::class,
        ListUserTagsTool::class,
        ShowUserTool::class,
        UpdateUserTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
