<?php

namespace App\Http\Resources\App;

use App\Http\Resources\Concerns\CollectsPaginated;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    use CollectsPaginated;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->rawTotal(),
            'currency' => $this->currency,
            'date' => $this->date(),
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
        ];
    }
}
