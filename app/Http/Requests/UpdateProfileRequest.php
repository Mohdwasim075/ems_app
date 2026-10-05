<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        // Fetch current user ID for unique rule ignoring during updates
        $userId = $this->user()?->id ?? $this->route('user');

        return [
            'name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone_number' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'city' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'],
            'state' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'],
            'zip' => ['nullable', 'regex:/^[0-9]{5,6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            // Custom messages for 'name'
            'name.required' => 'Please enter your full name.',
            'name.min' => 'Your name must be at least 3 characters long.',
            'name.max' => 'Your name cannot exceed 50 characters.',

            // Custom messages for 'email'
            'email.required' => 'We need your email address to update your account.',
            'email.email' => 'Please provide a valid email address (e.g., user@example.com).',
            'email.unique' => 'This email address is already in use by another account.',

            // Custom messages for other fields
            'phone_number.regex' => 'Please enter a valid 10-digit phone number.',
            'city.regex' => 'City name can only contain letters, spaces, and hyphens.',
            'state.regex' => 'State name can only contain letters, spaces, and hyphens.',
            'zip.regex' => 'ZIP code must be a valid 5 or 6 digit number.',
        ];
    }
}
