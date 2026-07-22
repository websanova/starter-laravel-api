<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'calculated_at' => $this->calculated_at->toIso8601String(),
            'group' => $this->group,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
