<?php

namespace App\Mcp\Tools\App\Bookmark;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;

class CreateBookmarkTool extends ApiTool
{
    protected string $method = 'POST';

    protected string $uri = '/bookmarks';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.bookmark.create.description');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url' => $schema->string()
                ->description(__('mcp.app.tools.bookmark.create.params.url'))
                ->required(),
            'title' => $schema->string()
                ->description(__('mcp.app.tools.bookmark.create.params.title'))
                ->required(),
            'is_favorited' => $schema->boolean()
                ->description(__('mcp.app.tools.bookmark.create.params.is_favorited')),
            'tag_ids' => $schema->array()
                ->items($schema->integer())
                ->description(__('mcp.app.tools.bookmark.create.params.tag_ids')),
        ];
    }
}
