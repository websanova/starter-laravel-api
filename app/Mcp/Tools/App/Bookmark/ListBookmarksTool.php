<?php

namespace App\Mcp\Tools\App\Bookmark;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListBookmarksTool extends ApiTool
{
    protected string $method = 'GET';

    protected string $uri = '/bookmarks';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.bookmark.list.description');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description(__('mcp.app.tools.bookmark.list.params.search')),
            'favorited' => $schema->boolean()
                ->description(__('mcp.app.tools.bookmark.list.params.favorited')),
            'tag_id' => $schema->integer()
                ->description(__('mcp.app.tools.bookmark.list.params.tag_id')),
            'sort_by' => $schema->string()
                ->enum(['title', 'created_at'])
                ->description(__('mcp.app.tools.bookmark.list.params.sort_by')),
            'sort_dir' => $schema->string()
                ->enum(['asc', 'desc'])
                ->description(__('mcp.app.tools.bookmark.list.params.sort_dir')),
            'per_page' => $schema->integer()
                ->description(__('mcp.app.tools.bookmark.list.params.per_page')),
        ];
    }
}
