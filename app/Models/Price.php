<?php

namespace App\Models;

use App\Enums\PlanInterval;
use App\Support\ServiceResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

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
     * Get the plan this price belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Sync the Stripe price ID and amount from the product's default price.
     */
    public function sync(): ServiceResult
    {
        if (!$this->stripe_product_id) {
            return ServiceResult::error('price.missing_product');
        }

        try {
            $product = Cashier::stripe()->products->retrieve(
                $this->stripe_product_id,
                ['expand' => ['default_price']],
            );
        } catch (ApiErrorException) {
            return ServiceResult::error('price.sync_failed');
        }

        $price = $product->default_price;

        if (!$price) {
            return ServiceResult::error('price.no_default_price');
        }

        $this->update([
            'stripe_price_id' => $price->id,
            'amount' => $price->unit_amount ?? 0,
        ]);

        return ServiceResult::success($this);
    }
}
