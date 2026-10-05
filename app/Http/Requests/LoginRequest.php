<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', Password::min(6)],
        ];
    }

    public function message(): array
    {
        return [
            // Email error handling
            'email.required' => 'The email field is required',
            'email.email' => 'A valid email should be entered',

            // password error handling
            'password.required' => 'The password field should not be empty',
            'password.min' => 'Password should be min 6',
        ];
    }
}
