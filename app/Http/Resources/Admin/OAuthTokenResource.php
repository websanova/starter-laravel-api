<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Concerns\CollectsPaginated;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OAuthTokenResource extends JsonResource
{
    use CollectsPaginated;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'client_id' => $this->client_id,
            'client_name' => $this->client->name,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
            'id' => $this->id,
            'scopes' => $this->scopes,
        ];
    }
}
