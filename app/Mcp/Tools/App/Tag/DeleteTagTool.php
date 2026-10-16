<?php

namespace App\Mcp\Tools\App\Tag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete a tag the authenticated user owns. The tag is removed from every bookmark that uses it.')]
#[IsDestructive]
class DeleteTagTool extends ApiTool
{
    protected string $method = 'DELETE';

    protected string $uri = '/tags/{tag}';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tag' => $schema->integer()
                ->description('The id of the tag to delete.')
                ->required(),
        ];
    }
}
