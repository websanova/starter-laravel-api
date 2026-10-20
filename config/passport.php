<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Passport will use when
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Passport uses encryption keys while generating secure access tokens for
    | your application. By default, the keys are stored as local files but
    | can be set via environment variables when that is more convenient.
    |
    */

    'private_key' => env('PASSPORT_PRIVATE_KEY'),

    'public_key' => env('PASSPORT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Passport Database Connection
    |--------------------------------------------------------------------------
    |
    | By default, Passport's models will utilize your application's default
    | database connection. If you wish to use a different connection you
    | may specify the configured name of the database connection here.
    |
    */

    'connection' => env('PASSPORT_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Authorization Endpoints
    |--------------------------------------------------------------------------
    |
    | The frontend page an MCP Client opens in the browser to log in and
    | approve, keyed by the server names in config/mcp.php. The API serves
    | no html, so this is never an API route.
    |
    */

    'authorization_endpoints' => [
        'app' => env('FRONTEND_APP_URL', 'http://localhost:5173') . '/oauth/authorize',
        'admin' => env('FRONTEND_ADMIN_URL', 'http://localhost:5174') . '/oauth/authorize',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Lifetimes
    |--------------------------------------------------------------------------
    |
    | Access tokens expire in minutes and refresh tokens in days. Access tokens
    | are kept short so MCP clients actually exercise the refresh_token grant.
    |
    */

    'tokens_expire_in' => (int) env('PASSPORT_TOKENS_EXPIRE_IN', 60),

    'refresh_tokens_expire_in' => (int) env('PASSPORT_REFRESH_TOKENS_EXPIRE_IN', 30),

    /*
    |--------------------------------------------------------------------------
    | Unused Client Lifetime
    |--------------------------------------------------------------------------
    |
    | A client registered through /oauth/register that never got a token
    | expires in hours. Only needs to outlast a user finishing the login page.
    |
    */

    'unused_clients_expire_in' => (int) env('PASSPORT_UNUSED_CLIENTS_EXPIRE_IN', 24),

];
