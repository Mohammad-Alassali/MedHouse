<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'id' => "integer",
        'name' => "string",
        'photo' => "string",
        'phone_number' => "string",
        'token' => "string"
    ])]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'photo' => $this['photo'] == null ? 'no_photo' : $this['photo'],
            'phone_number' => $this['phone_number'],
            'token' => User::query()->find($this['id'])->createToken('medHouseProject')->accessToken
        ];
    }
}
