<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'avatar_url' => $this->avatar_url,
            'created_at' => $this->created_at,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'id' => $this->id,
            'last_name' => $this->last_name,
            'locale' => $this->locale,
            'role' => $this->roles->first()?->name,
            'timezone' => $this->timezone,
            'updated_at' => $this->updated_at,
        ];
    }
}
