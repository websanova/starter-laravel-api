<?php

namespace App\Http\Resources\App;

use App\Http\Resources\Concerns\CollectsPaginated;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    use CollectsPaginated;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->created_at,
            'data' => $this->data,
            'id' => $this->id,
            'read_at' => $this->read_at,
            'type' => $this->type,
        ];
    }
}

