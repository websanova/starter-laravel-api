<?php

namespace App\Models;

use App\Enums\PlanInterval;
use App\Enums\PlanSort;
use App\Enums\PlanTier;
use App\Enums\SortDirection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Plan extends Model
{
    use HasFactory;

    /**
     * Cache key for all plans.
     */
    protected static string $cacheKey = 'plans:all';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'monthly_price',
        'yearly_price',
        'features',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'monthly_price' => 'integer',
            'yearly_price' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Invalidate the plans cache when a plan is saved or deleted.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(fn () => Cache::forget(static::$cacheKey));
        static::deleted(fn () => Cache::forget(static::$cacheKey));
    }

    /**
     * Get all plans from cache.
     */
    public static function cached(): Collection
    {
        return Cache::rememberForever(static::$cacheKey, function () {
            return static::orderBy('sort_order')->get();
        });
    }

    /**
     * Get the free plan from cache.
     */
    public static function free(): ?self
    {
        return static::cached()->firstWhere('slug', PlanTier::Free->value);
    }

    /**
     * Get the users on this plan.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Sort by the given column and direction.
     */
    public function scopeSortBy(Builder $query, ?PlanSort $column = null, ?SortDirection $direction = null): void
    {
        $query->orderBy(
            ($column ?? PlanSort::SortOrder)->value,
            ($direction ?? SortDirection::Asc)->value
        );
    }

    /**
     * Filter by active status.
     */
    public function scopeForActive(Builder $query, ?bool $active): void
    {
        if (is_null($active)) {
            return;
        }

        $query->where('is_active', $active);
    }

    /**
     * Get the Stripe price ID for the given billing interval.
     */
    public function priceId(PlanInterval $interval): ?string
    {
        return match ($interval) {
            PlanInterval::Monthly => $this->stripe_monthly_price_id,
            PlanInterval::Yearly => $this->stripe_yearly_price_id,
        };
    }

    /**
     * Get a specific feature value.
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return data_get($this->features, $key, $default);
    }

    /**
     * Check if this is a free plan (no Stripe prices).
     */
    public function isFree(): bool
    {
        return is_null($this->stripe_monthly_price_id)
            && is_null($this->stripe_yearly_price_id);
    }
}
