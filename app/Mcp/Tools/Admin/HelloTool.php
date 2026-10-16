<?php

namespace App\Mcp\Tools\Admin;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class HelloTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $user = $request->user();

        return Response::text("Hello admin {$user->first_name}, you are user #{$user->id}.");
    }

    /**
     * Get the tool's description.
     */
    public function description(): string
    {
        return __('mcp.admin.tools.hello.description');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            //
        ];
    }
}
