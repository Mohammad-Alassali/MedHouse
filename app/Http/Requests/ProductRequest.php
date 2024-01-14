<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'classification_id' => ['required', 'exists:classifications,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'scientific_name' => ['required', 'string'],
            'commercial_name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'quantity' => ['required', 'integer'],
            'price' => ['required', 'numeric'],
            'expiration_date' => ['required', 'date'],
            'photo' => ['nullable', 'image'],
            'is_otc' => ['required', 'boolean'],
        ];
    }
}
