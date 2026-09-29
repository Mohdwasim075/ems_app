<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Get limit from query string 
        $limit = $request->query('limit', 5);

        $events = Event::select('id', 'title', 'description', 'start_at', 'image', 'price', 'location')
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
                'image' => $event->image,
                // 'capacity' => $event->capacity,
                'available_seats' => $event->available_seats,
                'price' => $event->price
            ],
        ]);
    }

    public function top_events()
    {

        // $events = DB::table('events')
        // ->select('id', 'title','description', 'start_at','cover_image','location')
        //  ->where('status', 'published')
        //  ->where('featured_at', true)
        // ->orderBy('start_at', 'asc')
        // ->get();

        $events  = Event::select([
                'id',
                'title',
                'description',
                'start_at',
                'image',
                'location',
            ])
                ->where('status', 'published')
                ->withSum('registrations', 'quantity')
                ->orderByDesc('registrations_sum_quantity')
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
        $events =Event::select('id', 'title', 'description', 'start_at', 'image', 'location')
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
    //     dd([
    //     'all' => $request->all(),
    //     'title' => $request->input('title'),
    //     'category_id' => $request->input('category_id'),
    //     'price' => $request->input('price'),
    //     'status' => $request->input('status'),
    //     'location' => $request->input('location'),
    //     'start_at' => $request->input('start_at'),
    //     'end_at' => $request->input('end_at'),
    //     'capacity' => $request->input('capacity'),
    //     'description' => $request->input('description'),
    //     'has_image' => $request->hasFile('image'),
    //     'content_type' => $request->header('Content-Type'),
    // ]);

        // Custom backend validation messages
        $customMessages = [
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
             'image' => ['required','image','mimes:jpg,jpeg,png,webp','max:2048',
    ],
        ], $customMessages);

        //  Handle file upload
        if ($request->hasFile('image')) {
            // Stores the file in storage/app/public/events
           $path = $request->file('image')
                ->store('events', 'public');
            $validated['image'] = $path;
        }
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

    //dd([
    //     'all' => $request->all(),
    //     'title' => $request->input('title'),
    //     'category_id' => $request->input('category_id'),
    //     'price' => $request->input('price'),
    //     'status' => $request->input('status'),
    //     'location' => $request->input('location'),
    //     'start_at' => $request->input('start_at'),
    //     'end_at' => $request->input('end_at'),
    //     'capacity' => $request->input('capacity'),
    //     'description' => $request->input('description'),
    //     'has_image' => $request->hasFile('image'),
    //     'content_type' => $request->header('Content-Type'),
    // ]);
        //  Find the existing event or fail with 404
        $event = Event::findOrFail($id);

        // Validate all request attributes
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image'       =>['nullable','image','mimes:jpeg,png,webp','max:2048'],
            'start_at' => ['required', 'date'], // Changed from date_format
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'category_id'=>['required', 'integer', 'exists:categories,id'],
            'capacity'=>['required', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
        ]);

        // Handle Image Update
    if ($request->hasFile('image')) {
        // Delete old image if exists
        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }

        // Store new image
         $path = $request->file('image')
                ->store('events', 'public');
        $validated['image'] = $path;
    }

        // Convert datetime-local format (YYYY-MM-DDTHH:mm) to MySQL format (YYYY-MM-DD HH:mm:ss)
        $validated['start_at'] = Carbon::parse($validated['start_at'])->format('Y-m-d H:i:s');
        $validated['end_at'] = Carbon::parse($validated['end_at'])->format('Y-m-d H:i:s');
        //Update the event 

        if($validated['capacity'] > $event->capacity ){

            $validated['available_seats'] = ($validated['capacity'] - $event->capacity) + $event->available_seats;

        }

        if($validated['capacity'] <  $event->capacity ){


            $validated['available_seats'] = $event->available_seats  - ($event->capacity - $validated['capacity'])  ;

        }

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
