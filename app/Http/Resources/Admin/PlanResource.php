<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'stripe_monthly_price_id' => $this->stripe_monthly_price_id,
            'stripe_yearly_price_id' => $this->stripe_yearly_price_id,
            'monthly_price' => $this->monthly_price,
            'yearly_price' => $this->yearly_price,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'is_complimentary' => $this->is_complimentary,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
