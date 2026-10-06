<?php

use App\Mcp\Servers\AdminServer;
use App\Mcp\Servers\AppServer;
use Laravel\Mcp\Facades\Mcp;

// Mcp::web('/mcp/demo', \App\Mcp\Servers\PublicServer::class);

// routes/ai.php is loaded outside the api middleware group, so throttle:api is applied explicitly.
Mcp::web('/mcp', AppServer::class)
    ->middleware(['throttle:api', 'auth:sanctum', 'track-active', 'verified', 'password-updated']);

Mcp::web('/admin/mcp', AdminServer::class)
    ->middleware(['throttle:api', 'auth:sanctum', 'track-active', 'verified', 'password-updated', 'admin']);
