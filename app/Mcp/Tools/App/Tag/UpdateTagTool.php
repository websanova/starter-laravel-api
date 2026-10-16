<?php

namespace App\Mcp\Tools\App\Tag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
class UpdateTagTool extends ApiTool
{
    protected string $method = 'PATCH';

    protected string $uri = '/tags/{tag}';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.tag.update.description');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tag' => $schema->integer()
                ->description(__('mcp.app.tools.tag.update.params.tag'))
                ->required(),
            'name' => $schema->string()
                ->description(__('mcp.app.tools.tag.update.params.name')),
        ];
    }
}
