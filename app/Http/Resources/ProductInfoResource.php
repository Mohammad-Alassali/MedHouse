<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductInfoResource extends JsonResource
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
            'scientific_name' => $this['scientific_name'],
            'commercial_name' => $this['commercial_name'],
            'description' => $this['description'],
            'quantity' => $this['quantity'],
            'price' => $this['price'],
            'expiration_date' => $this['expiration_date'],
            'photo' => $this['photo'],
            'is_fav' => $this->is_fav(auth()->id()),
            'company' => new CompanyResource($this['company']),
            'classification' => new classificationResource($this['classification'])
        ];
    }
}
