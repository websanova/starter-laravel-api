<?php

namespace App\Mcp\Tools\App\Profile;

use App\Mcp\Tools\ApiTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Description('Update the authenticated user\'s profile. Only the fields provided are changed.')]
#[IsIdempotent]
class UpdateProfileTool extends ApiTool
{
    protected string $method = 'PATCH';

    protected string $uri = '/profile';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()
                ->description('The new first name of the user.'),
            'last_name' => $schema->string()
                ->description('The new last name of the user.'),
            'locale' => $schema->string()
                ->description('The new locale of the user, such as "en".'),
            'timezone' => $schema->string()
                ->description('The new timezone of the user as an IANA identifier, such as "America/Toronto".'),
        ];
    }
}
