<?php

namespace App\Mcp\Tools\Admin\User;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Show a user, including their plan, roles and subscriptions.')]
#[IsReadOnly]
class ShowUserTool extends ApiTool
{
    protected string $method = 'GET';

    protected string $uri = '/admin/users/{user}';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'user' => $schema->integer()
                ->description('The id of the user to show.')
                ->required(),
        ];
    }
}
