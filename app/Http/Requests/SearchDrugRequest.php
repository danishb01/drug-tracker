<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchDrugRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'drug_name' => 'required|string|min:2|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'drug_name.required' => 'Drug name is required.',
            'drug_name.min' => 'Drug name must be at least 2 characters.',
            'drug_name.max' => 'Drug name cannot exceed 100 characters.',
        ];
    }
}
