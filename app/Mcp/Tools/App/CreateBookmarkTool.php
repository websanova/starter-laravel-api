<?php

namespace App\Mcp\Tools\App;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create a bookmark for the authenticated user.')]
class CreateBookmarkTool extends ApiTool
{
    protected string $method = 'POST';

    protected string $uri = '/bookmarks';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url' => $schema->string()
                ->description('The url of the bookmark. Must be unique among the user\'s bookmarks.')
                ->required(),
            'title' => $schema->string()
                ->description('The title of the bookmark.')
                ->required(),
            'is_favorited' => $schema->boolean()
                ->description('Whether the bookmark is favorited.'),
            'tag_ids' => $schema->array()
                ->items($schema->integer())
                ->description('The tag ids for the bookmark.'),
        ];
    }
}
