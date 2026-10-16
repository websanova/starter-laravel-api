<?php

namespace App\Mcp\Tools\App\Bookmark;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
class UpdateBookmarkTool extends ApiTool
{
    protected string $method = 'PATCH';

    protected string $uri = '/bookmarks/{bookmark}';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.bookmark.update.description');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'bookmark' => $schema->integer()
                ->description(__('mcp.app.tools.bookmark.update.params.bookmark'))
                ->required(),
            'url' => $schema->string()
                ->description(__('mcp.app.tools.bookmark.update.params.url')),
            'title' => $schema->string()
                ->description(__('mcp.app.tools.bookmark.update.params.title')),
            'is_favorited' => $schema->boolean()
                ->description(__('mcp.app.tools.bookmark.update.params.is_favorited')),
            'tag_ids' => $schema->array()
                ->items($schema->integer())
                ->description(__('mcp.app.tools.bookmark.update.params.tag_ids')),
        ];
    }
}
