<?php

namespace App\Mcp\Tools\App\Profile;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Show the authenticated user\'s profile, including their plan and subscriptions.')]
#[IsReadOnly]
class ShowProfileTool extends ApiTool
{
    protected string $method = 'GET';

    protected string $uri = '/profile';

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
