<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Laravel\Mcp\Server\Registrar;

class AuthorizationMetadataController extends Controller
{
    /**
     * Describe the authorization server of the MCP server at the given path.
     * Only the authorization endpoint differs per server, it names the
     * frontend page the MCP Client opens in the browser.
     */
    public function show(string $path): JsonResponse
    {
        $server = array_search($path, config('mcp.servers'));

        abort_if($server === false, 404);

        return response()->json([
            'issuer' => url($path),
            'authorization_endpoint' => config("passport.authorization_endpoints.{$server}"),
            'token_endpoint' => route('passport.token'),
            'registration_endpoint' => route('mcp.oauth.register'),
            'response_types_supported' => ['code'],
            'code_challenge_methods_supported' => ['S256'],
            'scopes_supported' => [Registrar::OAUTH_SCOPE],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
        ]);
    }
}
