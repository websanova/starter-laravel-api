<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->amount,
            'id' => $this->id,
            'interval' => $this->interval,
            'stripe_price_id' => $this->stripe_price_id,
            'stripe_product_id' => $this->stripe_product_id,
        ];
    }
}
