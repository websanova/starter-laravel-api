<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Passport's view of a user. It reads the same users table as User but is its
 * own model because Passport's HasApiTokens cannot share a class with Sanctum's.
 * The ResolveOAuthUser middleware swaps it for the real User on OAuth requests.
 */
class OAuthUser extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens;

    protected $table = 'users';
}
