<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'number' => $this['number'],
            'total' => $this['total'],
            'amount' => $this['amount'],
            'status' => $this['status'],
            'paid' => $this['paid'],
            'date' => $this['date'],
            'orders' => OrderResource::collection($this->orders)
        ];
    }
}
