<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait CollectsPaginated
{
    /**
     * Shape a paginator into the API response payload with trimmed meta.
     */
    public static function paginated(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => static::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
