<?php

namespace App\Models;

use App\Enums\PlanInterval;
use App\Enums\PlanSort;
use App\Enums\PlanTier;
use App\Enums\SortDirection;
use App\Support\ServiceResult;
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
            'is_active' => 'boolean',
            'is_public' => 'boolean',
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
     * Sync prices from Stripe, optionally limited to a single interval.
     */
    public function sync(?PlanInterval $interval = null): ServiceResult
    {
        $prices = $this->prices()
            ->whereNotNull('stripe_product_id')
            ->when($interval, fn ($query) => $query->where('interval', $interval->value))
            ->get();

        foreach ($prices as $price) {
            $result = $price->sync();

            if (!$result->success) {
                return $result;
            }
        }

        return ServiceResult::success($this);
    }

    /**
     * Get a specific feature value.
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return data_get($this->features, $key, $default);
    }

    /**
     * Whether this plan has no Stripe prices.
     */
    protected function hasNoPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->prices->whereNotNull('stripe_price_id')->isEmpty(),
        );
    }

    /**
     * Whether this plan is a complimentary (non-free, no price) plan.
     */
    protected function isComplimentary(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->has_no_price
                && $this->slug !== PlanTier::Free->value,
        );
    }
}
