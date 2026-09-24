<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class Usercontroller extends Controller
{


    public function getmyEvents(Request $request)
    {
        // Get limit from query string (defaults to 10 if not provided)
        $limit = $request->query('limit', 10);

        $user = $request->user();

        $registrations = $user->registrations()->with('event')->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'My Events fetched successfully!',
            'data' => $registrations->items(), // Array of category records
            'pagination' => [
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
                'from' => $registrations->firstItem(),
                'to' => $registrations->lastItem(),
            ],
        ]);

    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,

            ],
        ]);

    }

    public function updateProfile(Request $request)
    {

        $user = User::findOrFail(Auth::id());

        // Validation Rules
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],

            'phone_number' => ['nullable', 'regex:/^[0-9]{10}$/'],


            'city' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'],
            'state' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z\s\-]+$/'],
            'zip' => ['nullable', 'regex:/^[0-9]{5,6}$/'],
        ], [
            // Custom messages for 'name'
            'name.required' => 'Please enter your full name.',
            'name.min' => 'Your name must be at least 3 characters long.',
            'name.max' => 'Your name cannot exceed 50 characters.',

            // Custom messages for 'email'
            'email.required' => 'We need your email address to update your account.',
            'email.email' => 'Please provide a valid email address (e.g., user@example.com).',
            'email.unique' => 'This email address is already in use by another account.',

            // Custom error messages
            'phone_number.regex' => 'Please enter a valid 10-digit phone number.',
            'city.regex' => 'City name can only contain letters, spaces, and hyphens.',
            'state.regex' => 'State name can only contain letters, spaces, and hyphens.',
            'zip.regex' => 'ZIP code must be a valid 5 or 6 digit number.',
        ]);

        //  Update User Record
        $user->update($validatedData);

        // Return Standard JSON Response for AJAX
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',

        ], 200);

    }

    public function updatePassword(Request $request)
    {

        // validation
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.required' => 'Please enter a new password',
            'password.confirmed' => 'The new password confirmation does not match',
        ]);

        // Update Password
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Return JSON Response for AJAX

        return response()->json([
            'success' => true,
            'message' => 'Password update successfully!',
        ], 200);

    }

    public function getusers(Request $request)
    {

        // Get limit from query string 
        $limit = $request->query('limit', 5);

        // get user with role:id, name
        $users = User::with('role:id,name')->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Users fetched successfully!',
            'data' => $users->items(), // 
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ], 200);
    }

    public function getuser(string $id)
    {

        // get user data from db with role relations
        $user = User::with('role:id,name')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User fetched successfully!',
            'data' => $user,
        ], 200);

    }

    public function getRoles()
    {
        $roles = Role::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ], 200);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8'],
        ]);


        $validated['password'] = bcrypt($validated['password']);


        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!',
            'data' => $user->load('role:id,name'),
        ], 201);

    }

    public function updateUser(Request $request, string $id)
    {

        // Find user record
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        //  Validate the request attributes
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        // Update the user profile
        $user->update($validated);

        // Return updated user with role relation loaded
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
            'data' => $user->load('role:id,name'),
        ], 200);
    }

    public function deleteuser(string $id)
    {

        $userId = (int) $id;
        $currentUser = Auth::user();

        // prevent  current user from deleting their own 

        if ($currentUser && $currentUser->id === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Action denied: You cannot delete your own account.',
            ], 400);
        }

        // get the current user with role and registrations
        $user = User::with(['role', 'registrations'])->find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // handle if the user is an attendee with active event registrations
        $roleName = strtolower($user->role ? $user->role->name : '');

        if ($roleName === 'attendee' || $user->registrations()->exists()) {
            $hasActiveBookings = $user->registrations()
                ->whereIn('status', ['CONFIRMED'])
                ->exists();

            if ($hasActiveBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user: This attendee has active event registrations/bookings.',
                ], 422);
            }
        }


        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ], 200);
    }
}
