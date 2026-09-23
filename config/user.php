<?php

use App\Enums\BookmarkSort;
use App\Enums\BookmarkView;
use App\Enums\SortDirection;
use App\Enums\UserSort;

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
    | Adding a locale here also needs a Stripe locale mapping added to
    | App\Services\Stripe\CreateSessionService.
    |
    */

    'supported_locales' => explode(',', env('USER_SUPPORTED_LOCALES', 'en-US,en-CA,fr-CA')),

    /*
    |--------------------------------------------------------------------------
    | Preferences
    |--------------------------------------------------------------------------
    |
    | Saved client display state, keyed by scope. Only the keys a user has
    | actually changed are stored on their row, and these fill in the rest at
    | read time. Adding or removing a key here takes effect on the next
    | request without a migration or a backfill over existing rows.
    |
    */

    'preferences' => [

        'admin' => [
            'users_sort_by' => UserSort::CreatedAt->value,
            'users_sort_dir' => SortDirection::Desc->value,
        ],

        'app' => [
            'bookmarks_sort_by' => BookmarkSort::CreatedAt->value,
            'bookmarks_sort_dir' => SortDirection::Desc->value,
            'bookmarks_view' => BookmarkView::Expanded->value,
        ],

    ],

];
