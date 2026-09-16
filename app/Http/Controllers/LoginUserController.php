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
        'password' => ['required',Password::min(6)],
    ],[ 
        //Email error handling
        'email.required' => 'The email field is required',
        'email.email' => 'A valid email should be entered',

        //password error handling
        'password.required' => 'The password field should not be empty',
        'password.min' => 'Password should be min 6'
    ]
    );

    if (!Auth::attempt($attributes)) {
        return response()->json([
            'message' => 'Invalid email or password'
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

       public function getmyEvents(Request $request){
         // Get limit from query string (defaults to 10 if not provided)
             $limit = $request->query('limit', 10);
    
             $user = $request->user();

            $registrations = $user->registrations()->with('event')->paginate($limit);
                

            return response()->json([
                'success' => true,
                'message' => 'My Events fetched successfully!',
               'data'    => $registrations->items(), // Array of category records
        'pagination' => [
            'current_page' => $registrations->currentPage(),
            'last_page'    => $registrations->lastPage(),
            'per_page'     => $registrations->perPage(),
            'total'        => $registrations->total(),
            'from'         => $registrations->firstItem(),
            'to'           => $registrations->lastItem(),
        ]
            ]);

        }

        public function getProfile(Request $request){
             $user = $request->user();

        return response()->json([
            'user' => [
                 'name'       => $user->name,
                'email'      => $user->email,
                'phone_number' => $user->phone_number,
                'city' =>  $user->city,
                'state' => $user->state,
                'zip' =>  $user->zip

            ]
        ]);
               
            }

      public function destroy(Request $request)
    {
     Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}

