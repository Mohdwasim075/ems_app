<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            // Custom Error Messages
            'name.required' => 'Category name is required',
            'name.max' => 'Category name cannot exceed 50 characters',
            'description.max' => 'Description cannot exceed 255 characters',
            'is_active.boolean' => 'Invalid status value provided',
        ];

    }
}
