<?php

namespace App\Mcp\Tools\App;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete a bookmark the authenticated user owns.')]
#[IsDestructive]
class DeleteBookmarkTool extends ApiTool
{
    protected string $method = 'DELETE';

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
                ->description('The id of the bookmark to delete.')
                ->required(),
        ];
    }
}
