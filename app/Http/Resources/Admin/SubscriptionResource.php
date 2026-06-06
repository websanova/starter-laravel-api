<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'stripe_id' => $this->stripe_id,
            'stripe_status' => $this->stripe_status,
            'stripe_price' => $this->stripe_price,
            'quantity' => $this->quantity,
            'trial_ends_at' => $this->trial_ends_at,
            'ends_at' => $this->ends_at,
            'on_grace_period' => $this->onGracePeriod(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
