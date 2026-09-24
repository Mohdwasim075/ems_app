<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Get limit from query string 
        $limit = $request->query('limit', 5);

        $events = Event::select('id', 'title', 'description', 'start_at', 'cover_image', 'price', 'location')
            ->where('status', 'published')->oldest()->paginate($limit);

        //     $events = DB::table('events')
        // ->select('id', 'title','description', 'start_at','price','cover_image','location')
        // ->orderBy('start_at')
        //         ->get();

        return response()->json([
            'success' => true,
            'data' => $events->items(), // Array of category records
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ],
        ]);

    }

    

    public function show(Event $event)
    {
    
      
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'event_date' => $event->start_at,
                'end_time' => $event->end_at,
                'location' => $event->location,
                // 'capacity' => $event->capacity,
                'available_seats' => $event->available_seats,
                'price' => $event->price
            ],
        ]);
    }

    public function featured()
    {

        // $events = DB::table('events')
        // ->select('id', 'title','description', 'start_at','cover_image','location')
        //  ->where('status', 'published')
        //  ->where('featured_at', true)
        // ->orderBy('start_at', 'asc')
        // ->get();

        $events = DB::table('events')
            ->leftJoin('event_registrations', 'events.id', '=', 'event_registrations.event_id')
            ->select(
                'events.id',
                'events.title',
                'events.description',
                'events.start_at',
                'events.cover_image',
                'events.location',
                DB::raw('COUNT(event_registrations.id) as total_registrations')
            )
            ->where('events.status', 'published')
            ->groupBy(
                'events.id',
                'events.title',
                'events.description',
                'events.start_at',
                'events.cover_image',
                'events.location'
            )
            ->orderByDesc('total_registrations')
            ->take(3)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Top 3 registered events fetched successfully.',
            'data' => $events,
        ]);
    }

    public function upcoming()
    {

        // get events which are published and hasn't yet started
        $events = DB::table('events')
            ->select('id', 'title', 'description', 'start_at', 'cover_image', 'location')
            ->where('status', 'published')
            ->where('start_at', '>=', now())
            ->orderBy('start_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }


     public function getEvents(Request $request)
    {
        if (! Event::exists()) {
            return response()->json([
                'message' => 'You have no events, please create events',

            ], 200);
        }

        // Get limit from query string (defaults to 10 if not provided)
        $limit = $request->query('limit', 10);

        // get the events from the database
        $events = Event::with('category:id,name')->oldest()->paginate($limit);

        // return as json response back

        return response()->json([
            'message' => 'Events fetched successfully!',
            'data' => $events->items(), // Array of category records
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ],
        ], 200);
    }


     public function createEvent(Request $request)
    {

        // Custom backend validation messages
        $customMessages = [
            'title.required' => 'Please provide a title for the event.',
            'category_id.required' => 'Select a valid category from the dropdown.',
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

        // Validation Rules
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'location' => ['required', 'string', 'max:50'],
            'start_at' => ['required', 'date'], // Changed from date_format
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], $customMessages);

        // Format dates for MySQL
        $validated['start_at'] = Carbon::parse($validated['start_at'])->format('Y-m-d H:i:s');
        $validated['end_at'] = Carbon::parse($validated['end_at'])->format('Y-m-d H:i:s');

        $validated['available_seats'] = $validated['capacity'];
        $validated['organizer_id'] = $request->user()->id;
        // Create Event Record
        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created successfully!',
            'data' => $event->load('category'),
        ], 201);
    }


     public function getEvent(Request $request, string $id)
    {

        //  Find the existing event or fail with 404
        $event = Event::with('category:id,name')->findOrFail($id);

       

        return response()->json([
            'message' => 'specific event fetched successfully!',
            'data' => $event,
        ]);

    }

      public function updateEvent(Request $request, string $id)
    {

        //  Find the existing event or fail with 404
        $event = Event::findOrFail($id);

        // Validate all request attributes
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_at' => ['required', 'date'], // Changed from date_format
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'category_id'=>['required', 'integer', 'exists:categories,id'],
            'capacity'=>['required', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
        ]);

        // Convert datetime-local format (YYYY-MM-DDTHH:mm) to MySQL format (YYYY-MM-DD HH:mm:ss)
        $validated['start_at'] = Carbon::parse($validated['start_at'])->format('Y-m-d H:i:s');
        $validated['end_at'] = Carbon::parse($validated['end_at'])->format('Y-m-d H:i:s');
        //Update the event 

        $event->update($validated);

        // Return  JSON response 
        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully!',
        ], 200);
    }


     public function deleteEvent(Request $request, string $id)
    {

        // dd('delete event');
        // Find the event with the id
        $event = Event::findOrFail($id);

        // if event registrations are there then show custom response
        if ($event->registrations()->exists()) {
            return response()->json([
                'message' => 'cannot delete event which has event registrations!.',

            ], 409);

        }

        // delete the event
        $event->delete();

        return response()->json([
            'message' => 'event deleted successfully!.',
        ], 200);

    }

   
}
