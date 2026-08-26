<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
     public function index()
    {
        $events = DB::table('events')
    ->select('id', 'title','description', 'start_at','cover_image','location')
    ->orderBy('start_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events
    ]);}

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
            'capacity' => $event->capacity,
            'category' => $category
        ]
    ]);
}

    public function featured()
        {
        

        $events = DB::table('events')
        ->select('id', 'title','description', 'start_at','cover_image','location')
         ->where('status', 'published')
         ->where('featured_at', true)
        ->orderBy('start_at', 'asc')
        ->get();
            return response()->json([
                'success' => true,
                'data' => $events
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
        
}
