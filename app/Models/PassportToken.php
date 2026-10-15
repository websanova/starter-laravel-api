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
     * Only tokens that are not revoked and still usable. Access tokens are
     * short lived, so a token with a refresh token stays active for as long as
     * that refresh token is live. A token without one is active until it
     * expires.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('revoked', false)
            ->where(function ($query) {
                $query->whereHas('refreshToken', function ($query) {
                    $query->where('revoked', false)
                        ->where('expires_at', '>', now());
                })->orWhere(function ($query) {
                    $query->whereDoesntHave('refreshToken')
                        ->where(function ($query) {
                            $query->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
            });
    }
}
