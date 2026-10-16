<?php

namespace App\Mcp\Tools\App\Tag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;

class CreateTagTool extends ApiTool
{
    protected string $method = 'POST';

    protected string $uri = '/tags';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.tag.create.description');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description(__('mcp.app.tools.tag.create.params.name'))
                ->required(),
        ];
    }
}
