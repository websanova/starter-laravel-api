<?php

namespace App\Mcp\Tools\Admin\UserTag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete a tag that belongs to a user. The tag is removed from every bookmark that uses it.')]
#[IsDestructive]
class DeleteUserTagTool extends ApiTool
{
    protected string $method = 'DELETE';

    protected string $uri = '/admin/users/{user}/tags/{tag}';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'user' => $schema->integer()
                ->description('The id of the user who owns the tag.')
                ->required(),
            'tag' => $schema->integer()
                ->description('The id of the tag to delete.')
                ->required(),
        ];
    }
}
