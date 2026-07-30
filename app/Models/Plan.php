<?php

namespace App\Models;

use App\Enums\PlanInterval;
use App\Enums\PlanSort;
use App\Enums\PlanTier;
use App\Enums\SortDirection;
use App\Enums\SubscriptionMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'is_active' => false,
        'is_public' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'features',
        'is_active',
        'is_public',
        'tier',
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
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'tier' => 'integer',
        ];
    }

    /**
     * Invalidate the plans cache when a plan is saved or deleted.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * Invalidate the cached plans.
     */
    public static function flushCache(): void
    {
        Cache::forget(static::$cacheKey);
    }

    /**
     * Get all plans from cache.
     */
    public static function cached(): Collection
    {
        return Cache::rememberForever(static::$cacheKey, function () {
            return static::with('prices')->orderBy('tier')->get();
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
     * Get the plans that may be listed publicly. Required mode has no free
     * tier to offer, so it is withheld even when the plan remains active.
     */
    public static function listable(): Collection
    {
        return static::cached()
            ->where('is_active', true)
            ->where('is_public', true)
            ->when(
                config('subscription.mode') === SubscriptionMode::Required,
                fn ($plans) => $plans->where('slug', '!=', PlanTier::Free->value)
            )
            ->values();
    }

    /**
     * Get the users on this plan.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the prices for this plan.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Sort by the given column and direction.
     */
    public function scopeSortBy(Builder $query, ?PlanSort $column = null, ?SortDirection $direction = null): void
    {
        $query->orderBy(
            ($column ?? PlanSort::Tier)->value,
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
     * Filter by public visibility.
     */
    public function scopeForPublic(Builder $query, ?bool $public): void
    {
        if (is_null($public)) {
            return;
        }

        $query->where('is_public', $public);
    }

    /**
     * Get the Stripe price ID for the given billing interval.
     */
    public function priceId(PlanInterval $interval): ?string
    {
        return $this->prices->firstWhere('interval', $interval)?->stripe_price_id;
    }

    /**
     * Resolve the plan owning a Stripe price ID.
     */
    public static function forPriceId(?string $priceId): ?self
    {
        if (is_null($priceId)) {
            return null;
        }

        return static::cached()
            ->first(fn ($plan) => $plan->prices->contains('stripe_price_id', $priceId));
    }

    /**
     * Resolve the billing interval for a Stripe price ID.
     */
    public static function intervalForPriceId(?string $priceId): ?PlanInterval
    {
        if (is_null($priceId)) {
            return null;
        }

        return static::cached()
            ->flatMap->prices
            ->firstWhere('stripe_price_id', $priceId)
            ?->interval;
    }

    /**
     * Get a specific feature value.
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return data_get($this->features, $key, $default);
    }

    /**
     * The public display name, translated per request or per recipient locale.
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => __('plans.' . $this->slug . '.name'),
        );
    }
}
