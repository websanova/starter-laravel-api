<?php

use Illuminate\Support\Facades\Schedule;

// --hours is how long an expired token is kept before deletion. A Sanctum token
// is useless once it expires and has no refresh token, so 1 hour is just a
// small grace buffer.
Schedule::command('sanctum:prune-expired --hours=1')->dailyAt('02:00')->timezone('America/New_York');

// --hours is the refresh token lifetime in hours. Access tokens are short lived,
// but an expired one is still tied to a live refresh token. Deleting it sooner
// would drop the connection from the admin oauth-tokens list while the client
// can still refresh. Follows PASSPORT_REFRESH_TOKENS_EXPIRE_IN.
Schedule::command('passport:purge --hours='.(config('passport.refresh_tokens_expire_in') * 24))->dailyAt('02:25')->timezone('America/New_York');

// Removes clients that registered through /oauth/register but never got a token. --hours is how old a
// client must be first, see PASSPORT_UNUSED_CLIENTS_EXPIRE_IN.
Schedule::command('passport:purge-oauth-clients --hours='.config('passport.unused_clients_expire_in'))->dailyAt('02:30')->timezone('America/New_York');

// Removes deleted accounts according to the configured prune strategy.
Schedule::command('users:prune-deleted')->dailyAt('02:05')->timezone('America/New_York');

// Back up the database, then clean out old backups.
Schedule::command('backup:run --only-db')->dailyAt('02:10')->timezone('America/New_York');
Schedule::command('backup:clean')->dailyAt('02:15')->timezone('America/New_York');

// Calculates the admin stats.
Schedule::command('stats:calculate')->dailyAt('02:20')->timezone('America/New_York');
