<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Category;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Get limit from query string
        $limit = $request->query('limit', 5);

        $events = Event::select('id', 'title', 'description', 'start_at', 'image', 'price', 'location')
            ->where('status', 'published')->oldest()->paginate($limit);

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
                'price' => $event->price,
            ],
        ]);
    }

    public function top_events()
    {

        $events = Event::select([
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
        $events = Event::select('id', 'title', 'description', 'start_at', 'image', 'location')
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

    public function createEvent(CreateEventRequest $request)
    {

        $validated = $request->validated();
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

    public function updateEvent(UpdateEventRequest $request, string $id)
    {

        // Find the existing event or fail with 404
        $event = Event::findOrFail($id);

        // Retrieve validated input array automatically
        $validated = $request->validated();

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
        // Update the event

       $capacityDiff = $validated['capacity'] - $event->capacity;
        if ($capacityDiff !== 0) {
            $validated['available_seats'] = max(0, $event->available_seats + $capacityDiff);
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
