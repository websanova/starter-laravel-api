<?php

use App\Enums\AccountPruneStrategy;
use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'sanctum'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Account Deletion
    |--------------------------------------------------------------------------
    |
    | Controls the behavior of self-service account deletion. The grace period
    | is the number of days a soft-deleted account can be restored by logging
    | in again. Set to 0 for immediate pruning with no restoration window.
    |
    | The prune strategy determines what happens when the grace period expires:
    | "delete" hard-deletes the user record, "anonymize" nulls PII fields
    | and replaces the email with a hash.
    |
    */

    'delete' => [
        'grace_period' => env('AUTH_DELETE_GRACE_PERIOD', 30),
        'prune_strategy' => AccountPruneStrategy::from(env('AUTH_DELETE_PRUNE_STRATEGY', 'anonymize')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Throttle
    |--------------------------------------------------------------------------
    |
    | The number of seconds between last_active_at updates. This prevents
    | a database write on every authenticated request.
    |
    */

    'activity_throttle' => env('AUTH_ACTIVITY_THROTTLE', 60),

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

    'user' => [
        'default_locale' => env('USER_DEFAULT_LOCALE', 'en_US'),
        'default_timezone' => env('USER_DEFAULT_TIMEZONE', 'America/New_York'),
    ],

];
