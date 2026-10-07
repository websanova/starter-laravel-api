<?php

use App\Mcp\Servers\AdminServer;
use App\Mcp\Servers\AppServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Mcp\Server\Http\Controllers\OAuthRegisterController;
use Laravel\Mcp\Server\Registrar;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);

// Discovery metadata and client registration, backed by Passport.
Mcp::oauthRoutes();

// The routes below replace the package's routes of the same URI, the later registration wins.

// The package points authorization_endpoint at the API's own Passport route. The API serves no html,
// so the metadata names the App authorize page instead.
$authorizationServerMetadata = fn () => response()->json([
    'issuer' => config('mcp.authorization_server') ?? url('/'),
    'authorization_endpoint' => config('app.frontend_url') . '/oauth/authorize',
    'token_endpoint' => route('passport.token'),
    'registration_endpoint' => url('/oauth/register'),
    'response_types_supported' => ['code'],
    'code_challenge_methods_supported' => ['S256'],
    'scopes_supported' => [Registrar::OAUTH_SCOPE],
    'grant_types_supported' => ['authorization_code', 'refresh_token'],
]);

Route::get('/.well-known/oauth-authorization-server', $authorizationServerMetadata)
    ->name('mcp.oauth.authorization-server');

Route::get('/.well-known/oauth-authorization-server/{path}', $authorizationServerMetadata)
    ->where('path', '.*')
    ->name('mcp.oauth.authorization-server.nested');

// Registration is public and creates a client per call, so it gets the auth throttle.
Route::post('/oauth/register', OAuthRegisterController::class)
    ->middleware('throttle:auth');

// routes/ai.php is loaded outside the api middleware group, so throttle:api is applied explicitly.
Mcp::web('/mcp', AppServer::class)
    ->middleware(['throttle:api', 'auth:api', 'oauth-user', 'track-active', 'verified', 'password-updated']);

Mcp::web('/admin/mcp', AdminServer::class)
    ->middleware(['throttle:api', 'auth:api', 'oauth-user', 'track-active', 'verified', 'password-updated', 'admin']);
