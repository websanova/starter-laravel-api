<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->created_at,
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
        ];
    }
}
