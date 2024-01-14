<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'id' => "mixed",
        'scientific_name' => "mixed",
        'commercial_name' => "mixed",
        'photo' => "mixed|string",
        'company' => "\App\Http\Resources\CompanyResource",
        'price' => "mixed",
        'is_fav' => "mixed"
    ])]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'scientific_name' => $this['scientific_name'],
            'commercial_name' => $this['commercial_name'],
            'photo' => $this['photo'] == null ? 'no_photo' : $this['photo'],
            'company' => new CompanyResource($this['company']),
            'price' => $this['price'],
            'is_fav' => $this->is_fav(auth()->id())
        ];
    }
}
