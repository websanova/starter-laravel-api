<?php

namespace App\Mcp\Tools\App\Profile;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
class UpdateProfileTool extends ApiTool
{
    protected string $method = 'PATCH';

    protected string $uri = '/profile';

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.app.tools.profile.update.description');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()
                ->description(__('mcp.app.tools.profile.update.params.first_name')),
            'last_name' => $schema->string()
                ->description(__('mcp.app.tools.profile.update.params.last_name')),
            'locale' => $schema->string()
                ->description(__('mcp.app.tools.profile.update.params.locale')),
            'timezone' => $schema->string()
                ->description(__('mcp.app.tools.profile.update.params.timezone')),
        ];
    }
}
