<?php

use App\Http\Controllers\Mcp\AuthorizationMetadataController;
use App\Http\Controllers\Mcp\ResourceMetadataController;
use App\Mcp\Servers\AdminServer;
use App\Mcp\Servers\AppServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Mcp\Server\Http\Controllers\OAuthRegisterController;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);

// MCP Clients are only given the server URL, so they find everything else through the discovery
// routes below and register themselves. This registers the package's versions of both, backed by Passport.
Mcp::oauthRoutes();

// The routes below replace the package's routes of the same URI, the later registration wins.

// First discovery step. The MCP Client arrives here from the resource_metadata URL in the 401 and asks
// which authorization server protects the MCP server at {path}. Each server is its own, see config/mcp.php.
// The route name is required, the package builds the resource_metadata URL in the 401 header from it.
Route::get('/.well-known/oauth-protected-resource/{path}', [ResourceMetadataController::class, 'show'])
    ->where('path', '.*')
    ->name('mcp.oauth.protected-resource.nested');

// Second discovery step. Lists the endpoints the MCP Client needs to register, log in and get tokens.
// The package points authorization_endpoint at the API's own authorize route, but the API serves no
// html, so it is the frontend page set in config/passport.php instead.
Route::get('/.well-known/oauth-authorization-server/{path}', [AuthorizationMetadataController::class, 'show'])
    ->where('path', '.*')
    ->name('mcp.oauth.authorization-server.nested');

// Where an MCP Client registers itself and gets its client_id, in place of the manual client setup of a
// normal OAuth API. It is public and creates a client per call, so it gets the auth throttle.
Route::post('/oauth/register', OAuthRegisterController::class)
    ->middleware('throttle:auth')
    ->name('mcp.oauth.register');

// routes/ai.php is loaded outside the api middleware group, so throttle:api is applied explicitly.
Mcp::web('/' . config('mcp.servers.app'), AppServer::class)
    ->middleware(['throttle:api', 'auth:api', 'oauth-user', 'track-active', 'verified', 'password-updated']);

Mcp::web('/' . config('mcp.servers.admin'), AdminServer::class)
    ->middleware(['throttle:api', 'auth:api', 'oauth-user', 'track-active', 'verified', 'password-updated', 'admin']);
