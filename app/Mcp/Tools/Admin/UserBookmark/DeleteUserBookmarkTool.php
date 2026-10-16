<?php

namespace App\Mcp\Tools\Admin\UserBookmark;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete a bookmark that belongs to a user.')]
#[IsDestructive]
class DeleteUserBookmarkTool extends ApiTool
{
    protected string $method = 'DELETE';

    protected string $uri = '/admin/users/{user}/bookmarks/{bookmark}';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'user' => $schema->integer()
                ->description('The id of the user who owns the bookmark.')
                ->required(),
            'bookmark' => $schema->integer()
                ->description('The id of the bookmark to delete.')
                ->required(),
        ];
    }
}
