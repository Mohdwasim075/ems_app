<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{

    public function index(Request $request)
    {
         // Get limit from query string (defaults to 10 if not provided)
         $limit = $request->query('limit', 5);

    $events = Event::select('id', 'title', 'description', 'start_at','cover_image', 'price', 'location')
                ->where('status', 'published')->oldest()->paginate($limit);
        
    //     $events = DB::table('events')
    // ->select('id', 'title','description', 'start_at','price','cover_image','location')
    // ->orderBy('start_at')
    //         ->get();

        return response()->json([
            'success' => true,
             'data'    => $events->items(), // Array of category records
        'pagination' => [
            'current_page' => $events->currentPage(),
            'last_page'    => $events->lastPage(),
            'per_page'     => $events->perPage(),
            'total'        => $events->total(),
            'from'         => $events->firstItem(),
            'to'           => $events->lastItem(),
        ]
    ]);
    
    }



    public function show(Event $event)
    {
        $category = Category::find($event->category_id);

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
                'price' => $event->price,
                'category' => $category
            ]
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
            'data'    => $events
            ]);
        }

     public function upcoming()
        {
        

        $events = DB::table('events')
        ->select('id', 'title','description', 'start_at','cover_image','location')
        ->where('status', 'published')
        ->where('start_at', '>=', now())
        ->orderBy('start_at', 'asc')
        ->get();


          return response()->json([
            'success' => true,
            'data' => $events
        ]);
        }
  
    public function getmyEvents(Request $request){
         $user = $request->user();

         dd($user);

        $registrations = $user->registrations()
            ->with('event')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $registrations
        ]);
    }
        
}
