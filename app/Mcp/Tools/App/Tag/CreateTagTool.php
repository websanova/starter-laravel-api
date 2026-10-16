<?php

namespace App\Mcp\Tools\App\Tag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create a tag for the authenticated user. List the existing tags first, since tag names must be unique. Returns the new tag id to use in the bookmark tools.')]
class CreateTagTool extends ApiTool
{
    protected string $method = 'POST';

    protected string $uri = '/tags';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('The name of the tag. Up to 50 characters, must start with a letter or number, and must be unique among the user\'s tags.')
                ->required(),
        ];
    }
}
