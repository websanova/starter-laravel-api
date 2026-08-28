<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'city' => $this->billing_city,
            'country' => $this->billing_country,
            'line1' => $this->billing_line1,
            'line2' => $this->billing_line2,
            'postal_code' => $this->billing_postal_code,
            'state' => $this->billing_state,
        ];
    }
}
