<?php

namespace App\Models;

use App\Enums\VerificationChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'code',
        'expires_at',
        'attempts',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => VerificationChannel::class,
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * The user this verification code belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
