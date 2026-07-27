<?php

namespace App\Http\Resources\App;

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
            'features' => $this->features,
            'id' => $this->id,
            'name' => $this->display_name,
            'prices' => (object) $this->prices->mapWithKeys(fn ($price) => [
                $price->interval->value => [
                    'amount' => $price->amount,
                    'currency' => $price->currency,
                ],
            ])->all(),
            'slug' => $this->slug,
        ];
    }
}

