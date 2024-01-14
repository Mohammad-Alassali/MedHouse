<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class EditPasswordRequest extends FormRequest
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
            'password' => "string",
            'new_password' => "string",
            'c_new_password' => "string"]
    )]
    public function rules(): array
    {
        return [
            'password' => 'required|min:8',
            'new_password' => 'required|min:8',
            'c_new_password' => 'required|same:new_password'
        ];
    }
}
