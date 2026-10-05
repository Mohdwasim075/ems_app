<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'location' => ['required', 'string', 'max:50'],
            'start_at' => ['required', 'date'], // Changed from date_format
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for the event.',
            'category_id.required' => 'Select a valid category from the dropdown.',
            'image.required' => 'Please provide a image',
            'category_id.exists' => 'The selected category does not exist.',
            'price.required' => 'Specify the ticket price (use 0 for free events).',
            'status.in' => 'Please select a valid status (published or draft).',
            'location.required' => 'Specify the event location',
            'start_at.required' => 'Please choose a start date and time.',
            'start_at.date_format' => 'Invalid start date format.',
            'end_at.required' => 'Please choose an end date and time.',
            'end_at.after_or_equal' => 'End date cannot be earlier than the start date.',
            'capacity.required' => 'Capacity is required.',
            'capacity.min' => 'Capacity must be at least 1 seat.',

        ];
    }
}
