<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Sanctum's personal access token, registered in AppServiceProvider so every
 * token Sanctum loads is this class and can carry the scopes below.
 */
class SanctumToken extends PersonalAccessToken
{
    protected $table = 'personal_access_tokens';

    /**
     * Only tokens that have not expired.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
