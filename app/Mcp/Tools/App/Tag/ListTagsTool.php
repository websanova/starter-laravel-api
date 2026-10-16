<?php

namespace App\Mcp\Tools\App\Tag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List the authenticated user\'s tags with their ids and names. Use this to find the ids needed for the tag_ids and tag_id fields on the bookmark tools.')]
#[IsReadOnly]
class ListTagsTool extends ApiTool
{
    protected string $method = 'GET';

    protected string $uri = '/tags';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
