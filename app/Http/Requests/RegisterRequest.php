<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    #[ArrayShape(['name' => "string",
        'phone_number' => "string",
        'password' => "string",
        'c_password' => "string",
        'photo' => "string"])]
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|alpha',
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10|unique:users',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
            'photo' => 'image'
        ];
    }
}
