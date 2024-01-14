<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'scientific_name' => $this->product['scientific_name'],
            'commercial_name' => $this->product['commercial_name'],
            'price' => $this->product['price'],
            'photo' => $this->product['photo'] == null ? 'no photo' : $this->product['photo'],
            'quantity' => $this['quantity'],
            'total' => $this['total']
        ];
    }
}
