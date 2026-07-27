<?php

namespace App\Models;

use App\Enums\PlanInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'plan_id',
        'interval',
        'stripe_product_id',
        'stripe_price_id',
        'amount',
        'currency',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'interval' => PlanInterval::class,
            'amount' => 'integer',
        ];
    }

    /**
     * Default the currency to the application currency when not set, and
     * invalidate the plans cache since it holds the plan prices.
     */
    protected static function booted(): void
    {
        static::creating(function (Price $price) {
            $price->currency ??= config('cashier.currency');
        });

        static::saved(fn () => Plan::flushCache());
        static::deleted(fn () => Plan::flushCache());
    }

    /**
     * Get the plan this price belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
