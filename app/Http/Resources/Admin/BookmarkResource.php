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
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'description' => $this->description,
            'id' => $this->id,
            'is_favorited' => $this->is_favorited,
            'title' => $this->title,
            'updated_at' => $this->updated_at,
            'url' => $this->url,
            'user_id' => $this->user_id,
        ];
    }
}
