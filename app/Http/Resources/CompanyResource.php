<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;
use StillCode\ArPhpLaravel\ArPhpLaravel;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape(['id' => "integer", 'name' => "string", 'icon' => "string"])]
    public function toArray(Request $request): array
    {
        $name = $this['name'];
        if (auth()->user()->lang == 'ar') {
            $name = ArPhpLaravel::en2ar($name);
        }
        return [
            'id' => $this['id'],
            'name' => $name,
            'icon' => $this['icon']
        ];
    }
}
