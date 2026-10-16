<?php

namespace App\Mcp\Tools\Admin\UserBookmark;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List a user\'s bookmarks with optional filtering and sorting.')]
#[IsReadOnly]
class ListUserBookmarksTool extends ApiTool
{
    protected string $method = 'GET';

    protected string $uri = '/admin/users/{user}/bookmarks';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'user' => $schema->integer()
                ->description('The id of the user whose bookmarks to list.')
                ->required(),
            'search' => $schema->string()
                ->description('Only return bookmarks matching this search term.'),
            'favorited' => $schema->boolean()
                ->description('Only return bookmarks that are favorited or not favorited.'),
            'tag_id' => $schema->integer()
                ->description('Only return bookmarks with this tag id. Look it up with the list user tags tool, never guess an id.'),
            'sort_by' => $schema->string()
                ->enum(['title', 'created_at'])
                ->description('The field to sort by.'),
            'sort_dir' => $schema->string()
                ->enum(['asc', 'desc'])
                ->description('The sort direction.'),
            'per_page' => $schema->integer()
                ->description('The number of bookmarks per page.'),
        ];
    }
}
