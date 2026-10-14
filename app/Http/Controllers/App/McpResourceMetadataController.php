<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Laravel\Mcp\Server\Registrar;

class McpResourceMetadataController extends Controller
{
    /**
     * Describe the MCP server at the given path. Each server is its own
     * authorization server, so its URL doubles as the issuer.
     */
    public function show(string $path): JsonResponse
    {
        abort_unless(in_array($path, config('mcp.servers')), 404);

        return response()->json([
            'resource' => url($path),
            'authorization_servers' => [url($path)],
            'scopes_supported' => [Registrar::OAUTH_SCOPE],
        ]);
    }
}
