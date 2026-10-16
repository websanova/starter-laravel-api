<?php

namespace App\Mcp\Tools\App\Tag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Description('Rename a tag the authenticated user owns.')]
#[IsIdempotent]
class UpdateTagTool extends ApiTool
{
    protected string $method = 'PATCH';

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
                ->description('The id of the tag to update.')
                ->required(),
            'name' => $schema->string()
                ->description('The new name of the tag. Up to 50 characters, must start with a letter or number, and must be unique among the user\'s tags.'),
        ];
    }
}
