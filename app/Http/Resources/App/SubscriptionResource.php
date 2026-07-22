<?php

namespace App\Http\Resources\App;

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
            'created_at' => $this->created_at,
            'ends_at' => $this->ends_at,
            'on_grace_period' => $this->onGracePeriod(),
            'quantity' => $this->quantity,
            'stripe_price' => $this->stripe_price,
            'stripe_status' => $this->stripe_status,
            'trial_ends_at' => $this->trial_ends_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

