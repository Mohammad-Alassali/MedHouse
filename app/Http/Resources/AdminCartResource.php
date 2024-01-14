<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class AdminCartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(['id' => "mixed",
            'user_name' => "mixed",
            'date' => "mixed",
            'status' => "mixed",
            'paid' => "mixed",
            'number' => "mixed"]
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'user_name' => $this['user']['name'],
            'date' => $this['date'],
            'status' => $this['status'],
            'paid' => $this['paid'],
            'number' => $this['number'],
        ];
    }
}
