<?php

use App\Enums\VerificationMode;

return [

    /*
    |--------------------------------------------------------------------------
    | Verification Mode
    |--------------------------------------------------------------------------
    |
    | Controls how user verification behaves.
    | - "disabled": no verification sent or enforced
    | - "auto": user is automatically marked verified on register
    | - "required": user must verify before accessing protected routes
    |
    */

    'mode' => VerificationMode::from(env('VERIFICATION_MODE', 'disabled')),

    /*
    |--------------------------------------------------------------------------
    | Verification Channels
    |--------------------------------------------------------------------------
    |
    | The channels through which the verification code is delivered.
    | One code is generated and sent to all enabled channels.
    |
    */

    'channels' => [
        'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | The number of seconds after registration during which an unverified
    | user can still access protected routes. Only applies when mode is
    | "required". Set to null or 0 to require immediate verification.
    |
    */

    'grace_period' => env('VERIFICATION_GRACE_PERIOD', null),

    /*
    |--------------------------------------------------------------------------
    | Code Length
    |--------------------------------------------------------------------------
    |
    | The number of digits in the verification code.
    |
    */

    'code_length' => env('VERIFICATION_CODE_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | Code Expiry
    |--------------------------------------------------------------------------
    |
    | The number of seconds before a verification code expires.
    |
    */

    'code_expiry' => env('VERIFICATION_CODE_EXPIRY', 900),

    /*
    |--------------------------------------------------------------------------
    | Resend Throttle
    |--------------------------------------------------------------------------
    |
    | The number of seconds a user must wait before requesting a new code.
    |
    */

    'resend_throttle' => env('VERIFICATION_RESEND_THROTTLE', 60),

    /*
    |--------------------------------------------------------------------------
    | Max Attempts
    |--------------------------------------------------------------------------
    |
    | The number of failed verification attempts before the code is
    | invalidated and a new one must be requested.
    |
    */

    'max_attempts' => env('VERIFICATION_MAX_ATTEMPTS', 5),

];
