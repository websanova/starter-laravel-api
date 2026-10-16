<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\App\Bookmark\CreateBookmarkTool;
use App\Mcp\Tools\App\Bookmark\DeleteBookmarkTool;
use App\Mcp\Tools\App\Bookmark\ListBookmarksTool;
use App\Mcp\Tools\App\Bookmark\UpdateBookmarkTool;
use App\Mcp\Tools\App\Profile\ShowProfileTool;
use App\Mcp\Tools\App\Profile\UpdateProfileTool;
use App\Mcp\Tools\App\Tag\CreateTagTool;
use App\Mcp\Tools\App\Tag\DeleteTagTool;
use App\Mcp\Tools\App\Tag\ListTagsTool;
use App\Mcp\Tools\App\Tag\UpdateTagTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('App Server')]
#[Version('0.0.1')]
#[Instructions('Tools for the authenticated user to manage their own account data. Bookmarks reference tags by id, never by name. Resolve tag names to ids with the list tags tool first, and create the tag if it does not exist.')]
class AppServer extends Server
{
    protected array $tools = [
        CreateBookmarkTool::class,
        CreateTagTool::class,
        DeleteBookmarkTool::class,
        DeleteTagTool::class,
        ListBookmarksTool::class,
        ListTagsTool::class,
        ShowProfileTool::class,
        UpdateBookmarkTool::class,
        UpdateProfileTool::class,
        UpdateTagTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
