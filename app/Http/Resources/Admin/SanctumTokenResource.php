<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Concerns\CollectsPaginated;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SanctumTokenResource extends JsonResource
{
    use CollectsPaginated;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'abilities' => $this->abilities,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
            'id' => $this->id,
            'last_used_at' => $this->last_used_at,
            'name' => $this->name,
        ];
    }
}
