<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Concerns\CollectsPaginated;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookmarkResource extends JsonResource
{
    use CollectsPaginated;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'is_favorited' => $this->is_favorited,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
