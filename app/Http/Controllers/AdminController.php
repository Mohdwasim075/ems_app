<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(){
       
        return view('admin.dashboard');

    }

    public function category(){
        return view('admin.categories');
    }

  
   

   

   

    

    public function getEvents(Request $request){
        if(! Event::exists()){
            return response()->json([
                'message' => 'You have no events, please create events'

            ], 200);
        }

         // Get limit from query string (defaults to 10 if not provided)
         $limit = $request->query('limit', 10);

        //get the events from the database
        $events = Event::with('category:id,name')->oldest()->paginate($limit);

        

        
        //return as json response back

        return response()->json([
            'message' => 'Events fetched successfully!',
            'data'    => $events->items(), // Array of category records
        'pagination' => [
            'current_page' => $events->currentPage(),
            'last_page'    => $events->lastPage(),
            'per_page'     => $events->perPage(),
            'total'        => $events->total(),
            'from'         => $events->firstItem(),
            'to'           => $events->lastItem(),
        ]
        ],200);
    }

    public function getEvent(Request $request, string $id){

        // 1. Find the existing event or fail with 404
        $event = Event::findOrFail($id);

        return response()->json([ 
            'message' => 'specific event fetched successfully!',
            'data' => $event
            ]);


    }

    public function createEvent(Request $request){


        // Custom backend validation messages
        $customMessages = [
            'title.required'        => 'Please provide a title for the event.',
            'category_id.required'  => 'Select a valid category from the dropdown.',
            'category_id.exists'    => 'The selected category does not exist.',
            'price.required'        => 'Specify the ticket price (use 0 for free events).',
            'status.in' => 'Please select a valid status (published or draft).',
            'location.required'     => 'Specify the event location',
            'start_at.required'     => 'Please choose a start date and time.',
            'start_at.date_format'  => 'Invalid start date format.',
            'end_at.required'       => 'Please choose an end date and time.',
            'end_at.after_or_equal' => 'End date cannot be earlier than the start date.',
            'capacity.required'     => 'Capacity is required.',
            'capacity.min'          => 'Capacity must be at least 1 seat.'
        ];

        // Validation Rules
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price'       => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'location'    => ['required', 'string', 'max:50'],
            'start_at'    => ['required', 'date'], // Changed from date_format
            'end_at'      => ['required', 'date', 'after_or_equal:start_at'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], $customMessages);

        // 2. Format dates for MySQL
    $validated['start_at'] = \Carbon\Carbon::parse($validated['start_at'])->format('Y-m-d H:i:s');
    $validated['end_at']   = \Carbon\Carbon::parse($validated['end_at'])->format('Y-m-d H:i:s');

        $validated['available_seats'] = $validated['capacity'];
        $validated['organizer_id']     = $request->user()->id;
        // Create Event Record
        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully!',
            'data'    => $event->load('category')
        ], 201);
    }

    public function updateEvent(Request $request, string $id)
{
   
    // 1. Find the existing event or fail with 404
    $event = Event::findOrFail($id);

    // 2. Validate all request attributes
    $validated = $request->validate([
        'title'        => ['required', 'string', 'max:150'],
        'description'  => ['nullable', 'string', 'max:1000'],
        'start_at'    => ['required', 'date'], // Changed from date_format
        'end_at'      => ['required', 'date', 'after_or_equal:start_at'],
        'location'     => ['nullable', 'string', 'max:50'],
        'price'        => ['required', 'numeric', 'min:0'],
        'status' => ['required', 'string'],
    ]);


    // Convert datetime-local format (YYYY-MM-DDTHH:mm) to MySQL format (YYYY-MM-DD HH:mm:ss)
    $validated['start_at'] = \Carbon\Carbon::parse($validated['start_at'])->format('Y-m-d H:i:s');
    $validated['end_at']   = \Carbon\Carbon::parse($validated['end_at'])->format('Y-m-d H:i:s');
    // 3. Update the event instance

    $event->update($validated);

    // 4. Return structured JSON response for jQuery AJAX
    return response()->json([
        'success' => true,
        'message' => 'Event updated successfully!',
    ], 200);
}

    public function deleteEvent(Request $request, string $id){

        // dd('delete event');
        // Find the event with the id 
        $event = Event::findOrFail($id);

        //if event registrations are there then show custom response
        if($event->registrations()->exists()){
            return response()->json([
                'message' => 'cannot delete event which has event registrations!.'

            ],409 );

        }



        //delete the event 
        $event->delete();

        return response()->json([
            'message' => 'event deleted successfully!.'
        ],200);


    }

    

    public function getReport(){


        //get users(attendee)'s count 
       $totalUsers = User::whereHas('role', function ($query) {
            $query->where('name', 'attendee'); // Matches role name in roles table
        })->count();
        //events
        $totalEvents = Event::count();

         $totalBookings = EventRegistration::all()
                               ->sum('quantity');
    

        //revenue
        $totalRevenue = EventRegistration::all()->sum('total_price');

               $data = [
            'total_users' => $totalUsers,
            'total_events' => $totalEvents,
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
        ];

        return response()->json($data);
        
    }

    public function getbookings(Request $request){

        // Get limit from query string (defaults to 10 if not provided)
        $limit = $request->query('limit', 10);

        $bookings = EventRegistration::with([
            'event:id,title', 
            'user:id,name'
        ])->latest()->paginate($limit);

        return response()->json([
            'message' => 'booking fetched successfully!',
           'data'    => $bookings->items(), // Array of category records
        'pagination' => [
            'current_page' => $bookings->currentPage(),
            'last_page'    => $bookings->lastPage(),
            'per_page'     => $bookings->perPage(),
            'total'        => $bookings->total(),
            'from'         => $bookings->firstItem(),
            'to'           => $bookings->lastItem(),
        ]
         ], 200);
        
    }

    public function getbooking(string $id){

    // Find registration or fail with 404
        $booking = EventRegistration::with([
            'event:id,title',
            'user:id,name'
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking details retrieved successfully.',
            'data'    => $booking
        ], 200);
    }
        
    public function deletebooking(string $id){
        return DB::transaction(function () use ($id) {
            // 1. Fetch booking with its parent event (pessimistic lock to prevent race conditions)
            $booking = EventRegistration::where('id', $id)->lockForUpdate()->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.'
                ], 404);
            }

            $event = $booking->event;

            // 2. Business Rule: Check if event has already started
            if (Carbon::parse($event->start_at)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a booking for an event that has already started.'
                ], 422);
            }

            // 3. Reclaim seats back to event capacity (only if booking was confirmed)
            if ($booking->status === 'CONFIRMED') {
                $event->increment('available_seats', $booking->quantity);
            }

    
            // 4. Delete record (Soft Delete recommended if using SoftDeletes trait)
            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully and seats restored.',
                'data'    => [
                    'restored_seats'  => $booking->quantity,
                    'available_seats' => $event->fresh()->available_seats
                ]
            ], 200);
        });



    }
    

   

    public function getusers(Request $request){

         // Get limit from query string (defaults to 5 if not provided)
         $limit = $request->query('limit', 5);

      // Eager load the 'role' relationship selecting only id and name
        $users = User::with('role:id,name')->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Users fetched successfully!',
           'data'    => $users->items(), // Array of category records
        'pagination' => [
            'current_page' => $users->currentPage(),
            'last_page'    => $users->lastPage(),
            'per_page'     => $users->perPage(),
            'total'        => $users->total(),
            'from'         => $users->firstItem(),
            'to'           => $users->lastItem(),
        ]
        ], 200);
    }

    public function getuser(string $id){

        //get user data from db with role relations
        $user = User::with('role:id,name')->find($id);

        if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'message' => 'User fetched successfully!',
        'data'    => $user // Returns object directly
    ], 200);



    }
    
    public function getRoles()
    {
        $roles = Role::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data'    => $roles
        ], 200);
    }

    public function createUser(Request $request){
          $validated = $request->validate([
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')], // Removed ->ignore()
        'phone_number' => ['nullable', 'string', 'max:20'],
        'role_id'      => ['required', 'integer', 'exists:roles,id'],
        'password'     => ['required', 'string', 'min:8'], // Ensure password is required for creation
    ]);

    // Hash password before saving
    $validated['password'] = bcrypt($validated['password']);

    // Create user record
    $user = User::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'User created successfully!',
        'data'    => $user->load('role:id,name')
    ], 201);




    }
    public function updateUser(Request $request, string $id)
    {
      
        // 1. Find user record
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // 2. Validate request parameters
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role_id'      => ['required', 'integer', 'exists:roles,id']
        ]);

     

        // 4. Update the record
        $user->update($validated);

        // 5. Return updated user with role relation loaded
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
            'data'    => $user->load('role:id,name')
        ], 200);
    }


    public function deleteuser(string $id){

        $userId = (int) $id;
        $currentUser = Auth::user();

        // Guard Rail 1: Prevent current user from deleting their own account
        if ($currentUser && $currentUser->id === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Action denied: You cannot delete your own account.'
            ], 400);
        }

        // Find the target user along with relations
        $user = User::with(['role', 'registrations'])->find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Guard Rail 2: Check if the user is an attendee with active event registrations
        $roleName = strtolower($user->role ? $user->role->name : '');
        
        if ($roleName === 'attendee' || $user->registrations()->exists()) {
            $hasActiveBookings = $user->registrations()
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($hasActiveBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user: This attendee has active event registrations/bookings.'
                ], 422);
            }
        }

        // Perform deletion (Soft Delete if configured on model, otherwise permanent)
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ], 200);
    }

    
}



