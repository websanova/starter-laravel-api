<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $coupon = $this->resource->coupon;

        return [
            'amount_off' => $coupon->amount_off,
            'code' => $this->resource->code,
            'currency' => $coupon->currency,
            'duration' => $coupon->duration,
            'duration_in_months' => $coupon->duration_in_months,
            'percent_off' => $coupon->percent_off,
        ];
    }
}

