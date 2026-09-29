<?php

namespace App\Http\Controllers;

use App\Jobs\SendPasswordResetEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
        ],
        );
        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json([
                'message' => 'Unable to process password reset request'
            ],422);
        }
         $token = Password::createToken($user);

        SendPasswordResetEmail::dispatch($user, $token);

        return response()->json([
            'message' => 'Password reset link sent successfully.'
        ]);



    }

    public function resetPassword(Request $request)
    {

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {

                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {

            return response()->json([
                'message' => 'Password reset successfully.',
            ]);
        }

        return response()->json([
            'message' => __($status),
        ], 422);
    }
}
