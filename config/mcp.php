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

];
