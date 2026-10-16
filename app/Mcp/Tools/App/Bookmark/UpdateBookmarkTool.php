<?php

namespace App\Mcp\Tools\App\Bookmark;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Description('Update a bookmark the authenticated user owns. Only the fields provided are changed.')]
#[IsIdempotent]
class UpdateBookmarkTool extends ApiTool
{
    protected string $method = 'PATCH';

    protected string $uri = '/bookmarks/{bookmark}';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'bookmark' => $schema->integer()
                ->description('The id of the bookmark to update.')
                ->required(),
            'url' => $schema->string()
                ->description('The new url of the bookmark. Must be unique among the user\'s bookmarks.'),
            'title' => $schema->string()
                ->description('The new title of the bookmark.'),
            'is_favorited' => $schema->boolean()
                ->description('Whether the bookmark is favorited.'),
            'tag_ids' => $schema->array()
                ->items($schema->integer())
                ->description('The full list of existing tag ids for the bookmark. Look them up with the list tags tool, never guess an id. Replaces the existing tags, and an empty list removes them all.'),
        ];
    }
}
