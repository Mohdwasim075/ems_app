<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    
        //dd($request->all());
       $request->validate([
        'event_id' => ['required', 'exists:events,id'],
        'quantity' => ['required', 'integer', 'min:1'],
    ]);

    $user = $request->user();
    $event = Event::findOrFail($request->event_id);

    

    $price = (float) $event->price;
    $quantity = (int) $request->quantity;

    if ($event->available_seats <= 0) {
    return response()->json([
        'message' => 'Seats are completelyfilled for this event.'
    ], 422);



    }
        // Check available capacity
    if ($quantity > $event->available_seats) {
        return response()->json([
            'message' => "Only {$event->available_seats} seat(s) remaining for this event."
        ], 422);
    }

   $existingRegistration = EventRegistration::where('event_id', $event->id)
    ->where('user_id', $user->id)
    ->exists();

if ($existingRegistration) {
    return response()->json([
        'message' => 'You have already registered for this event.'
    ], 409);
}
    // Create registration
    $registration = EventRegistration::create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'quantity' => $quantity,
        'unit_price' => $price,
        'total_price' => $price * $quantity,
    ]);

    // Generate registration number
    $registration->update([
        'registration_number' => 'REG-' . str_pad(
            $registration->id,
            6,
            '0',
            STR_PAD_LEFT
        ),
    ]);

    // Reduce event capacity
    $event->decrement('available_seats', $quantity);

    return response()->json([
        'message' => 'Registration successful',
        'registration' => $registration->fresh(),
    ], 201);

        
    }

    public function getBookings(){
       $totalBookings = EventRegistration::whereDate('created_at', today())
                               ->sum('quantity');
        dd($totalBookings);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
