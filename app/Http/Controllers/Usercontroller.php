<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class Usercontroller extends Controller
{
    public function updateProfile(Request $request)
    {

        $user = User::findOrFail(Auth::id());

        // 1. Validation Rules
        $validatedData = $request->validate([
           'name' => ['required', 'string', 'min:3', 'max:50','regex:/^[a-zA-Z\s\-]+$/'],
           'email' => ['required', 'email', 'unique:users,email,' . $user->id],
           // Phone: Required or nullable, matches 10-digit number format
            'phone_number' => ['nullable', 'regex:/^[0-9]{10}$/'], 

            // City: Only letters, spaces, and hyphens allowed (2 to 50 characters)
            'city'  => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'], 

            'state' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'],

            // ZIP / Postal Code: 6 digits (Indian PIN format) or 5 digits (US ZIP format)
            'zip'   => ['nullable', 'regex:/^[0-9]{5,6}$/'],
            [
            // Custom messages for 'name'
            'name.required' => 'Please enter your full name.',
            'name.min'      => 'Your name must be at least 3 characters long.',
            'name.max'      => 'Your name cannot exceed 50 characters.',

            // Custom messages for 'email'
            'email.required' => 'We need your email address to update your account.',
            'email.email'    => 'Please provide a valid email address (e.g., user@example.com).',
            'email.unique'   => 'This email address is already in use by another account.',

            // Custom error messages
            'phone_number.regex' => 'Please enter a valid 10-digit phone number.',
            'city.regex'  => 'City name can only contain letters, spaces, and hyphens.',
            'state.regex'  => 'City name can only contain letters, spaces, and hyphens.',
            'zip.regex'   => 'ZIP code must be a valid 5 or 6 digit number.',
             ]
        ]);

        // 3. Update User Record
        $user->update($validatedData);

        // 4. Return Standard JSON Response for AJAX
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            
        ], 200);
    
        

    }

    public function updatePassword(Request $request){

    // 1. validation 
    $validated = $request->validate([
        'current_password' => ['required', 'current_password'],
        'password' => ['required', 'confirmed', Password::min(6)],
        ],[
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.required'                 => 'Please enter a new password',
            'password.confirmed'                => 'The new password confirmation does not match',
        ]);

    // 2. Update Password
    $request->user()->update([
        'password' => Hash::make($validated['password'])
    ]);

    // 3. Return JSON Response for AJAX

    return response()->json([
        'success' => true,
        'message'=>'Password update successfully!'
    ], 200);
        

    }
}
