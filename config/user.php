<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Defaults
    |--------------------------------------------------------------------------
    |
    | Default locale and timezone applied when a user has not set their own.
    | The columns stay null in the database and these values are returned by
    | the model accessors as fallbacks. Timezone is an IANA identifier.
    |
    */

    'default_locale' => env('USER_DEFAULT_LOCALE', 'en-US'),
    'default_timezone' => env('USER_DEFAULT_TIMEZONE', 'America/New_York'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The locales a user is allowed to store as their preference. These are
    | BCP 47 language tags surfaced to the client and validated on update.
    | Distinct from app.supported_locales, which drives translation lookup.
    |
    */

    'supported_locales' => explode(',', env('USER_SUPPORTED_LOCALES', 'en-US,en-CA,fr-CA')),

];
