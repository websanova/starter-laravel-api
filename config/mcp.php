<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Redirect Domains
    |--------------------------------------------------------------------------
    |
    | The domains an MCP Client may register as a redirect URI. Claude's hosted
    | surfaces use claude.ai (claude.com is the announced successor) and Claude
    | Code uses a loopback address on a random port, which listing localhost
    | allows on any port.
    |
    */

    'redirect_domains' => [
        'https://claude.ai',
        'https://claude.com',
        'http://localhost',
    ],

    /*
    |--------------------------------------------------------------------------
    | Servers
    |--------------------------------------------------------------------------
    |
    | The MCP servers the API exposes, by name and path. The path is the
    | server's own URL and is also its authorization server (issuer) in the
    | discovery metadata. The authorization endpoint for each name lives in
    | config/passport.php.
    |
    */

    'servers' => [
        'app' => 'mcp',
        'admin' => 'admin/mcp',
    ],

];
