<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class VerifyingRegisterRequest extends FormRequest
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
    #[ArrayShape(['code' => "string", 'phone_number' => "string"])]
    public function rules(): array
    {
        return [
            'code' => 'required|min:6|max:6|regex:/^([0-9]*)$/',
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10',
        ];
    }
}
