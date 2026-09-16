<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterUserController extends Controller
{   

    public function index(){
        return view('auth.register');
    }

    public function store(Request $request){


        $validatedAttributes = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'password' => ['required', Password::min(6), 'confirmed'],
    ]);

    $user = User::create($validatedAttributes);

    Auth::login($user);

    return response()->json([
        'success' => true,
        'message' => 'User registered successfully!',
        'redirect' => url('/login'), // Send back target redirect URL
    ], 201);
    }
    public function create(){
        return;
    }
}
