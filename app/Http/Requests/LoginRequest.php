<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class LoginRequest extends FormRequest
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
    #[ArrayShape([
        'phone_number' => "string",
        'password' => "string"
    ])]
    public function rules(): array
    {
        return [
            'phone_number' => 'required|exists:users|string|regex:/^([0-9]*)$/|min:10|max:10',
            'password' => 'required|min:8'
        ];
    }
}
