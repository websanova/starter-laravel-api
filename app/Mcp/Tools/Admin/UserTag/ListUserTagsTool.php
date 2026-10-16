<?php

namespace App\Mcp\Tools\Admin\UserTag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List a user\'s tags with their ids and names. Use this to find the id needed for the tag_id field on the list user bookmarks tool.')]
#[IsReadOnly]
class ListUserTagsTool extends ApiTool
{
    protected string $method = 'GET';

    protected string $uri = '/admin/users/{user}/tags';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'user' => $schema->integer()
                ->description('The id of the user whose tags to list.')
                ->required(),
            'sort_by' => $schema->string()
                ->enum(['name'])
                ->description('The field to sort by.'),
            'sort_dir' => $schema->string()
                ->enum(['asc', 'desc'])
                ->description('The sort direction.'),
        ];
    }
}
