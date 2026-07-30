<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Concerns\CollectsPaginated;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    use CollectsPaginated;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->created_at,
            'features' => $this->features,
            'id' => $this->id,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'name' => $this->name,
            'prices' => PriceResource::collection($this->prices),
            'slug' => $this->slug,
            'tier' => $this->tier,
            'updated_at' => $this->updated_at,
        ];
    }
}
