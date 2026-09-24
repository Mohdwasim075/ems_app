<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {

       $validatedAttributes = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(6), 'confirmed'],
        ]);

        $roleId = Role::where('name', 'attendee')->firstOrFail()->id;

        $validatedAttributes['role_id'] = $roleId;

        $user = User::create($validatedAttributes);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully!',
            'redirect' => url('/login'),
        ], 201);
    }

   
}
