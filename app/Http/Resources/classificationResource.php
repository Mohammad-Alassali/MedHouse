<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Stichoza\GoogleTranslate\GoogleTranslate;

class classificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $name = $this['name'];
        $lang = new GoogleTranslate();
        if (auth()->user()->lang == 'ar') {
            $name = $lang->setTarget('ar')->setSource('en')->translate($name);;
        }
        return [
            'id' => $this['id'],
            'name' => $name,
        ];
    }
}
