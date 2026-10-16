<?php

namespace App\Mcp\Tools\App\Bookmark;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class DeleteBookmarkTool extends ApiTool
{
    protected string $method = 'DELETE';

    protected string $uri = '/bookmarks/{bookmark}';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.bookmark.delete.description');
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
                ->description(__('mcp.app.tools.bookmark.delete.params.bookmark'))
                ->required(),
        ];
    }
}
