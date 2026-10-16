<?php

namespace App\Mcp\Tools\App\Profile;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ShowProfileTool extends ApiTool
{
    protected string $method = 'GET';

    protected string $uri = '/profile';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.profile.show.description');
    }

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
