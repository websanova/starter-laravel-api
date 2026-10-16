<?php

namespace App\Mcp\Tools\App\Tag;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class DeleteTagTool extends ApiTool
{
    protected string $method = 'DELETE';

    protected string $uri = '/tags/{tag}';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.tag.delete.description');
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
                ->description(__('mcp.app.tools.tag.delete.params.tag'))
                ->required(),
        ];
    }
}
