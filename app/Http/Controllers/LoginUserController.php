<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class LoginUserController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {

        $attributes = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', Password::min(6)],
        ], [
            // Email error handling
            'email.required' => 'The email field is required',
            'email.email' => 'A valid email should be entered',

            // password error handling
            'password.required' => 'The password field should not be empty',
            'password.min' => 'Password should be min 6',
        ]
        );

        if (! Auth::attempt($attributes)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        $request->session()->regenerate();

        //  if (Auth::user()->role->name === 'admin') {
        //     return redirect()->route('admin.dashboard');
        // }

        // if (Auth::user()->role->name === 'attendee') {
        //     return redirect()->route('attendee.home');
        // }

        return response()->json([
            'message' => 'Login successful',
            'user' => Auth::user()->role->name,
        ]);
    }



    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
