<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Token;

/**
 * Passport's access token under a name that does not clash with Sanctum's
 * tokens. It reads the same oauth_access_tokens table.
 */
class PassportToken extends Token
{
    /**
     * Only tokens that are not revoked and have not expired.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('revoked', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
