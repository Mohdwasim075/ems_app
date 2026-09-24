<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{



    public function store(Request $request)
    {

        // dd($request->all());
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
                'message' => 'Seats are completely filled for this event.',
            ], 422);

        }
        // Check available capacity
        if ($quantity > $event->available_seats) {
            return response()->json([
                'message' => "Only {$event->available_seats} seat(s) remaining for this event.",
            ], 422);
        }

        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($existingRegistration) {
            return response()->json([
                'message' => 'You have already registered for this event.',
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

    public function getbookings(Request $request)
    {

        // Get limit from query string (defaults to 10 if not provided)
        $limit = $request->query('limit', 10);

        $bookings = EventRegistration::with([
            'event:id,title',
            'user:id,name',
        ])->latest()->paginate($limit);


        return response()->json([
            'message' => 'booking fetched successfully!',
            'data' => $bookings->items(),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
                'from' => $bookings->firstItem(),
                'to' => $bookings->lastItem(),
            ],
        ], 200);

    }

    public function getbooking(string $id)
    {

        // Find registration or fail with 404
        $booking = EventRegistration::with([
            'event:id,title',
            'user:id,name',
        ])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking details retrieved successfully.',
            'data' => $booking,
        ], 200);
    }

    public function deletebooking(string $id)
    {
        return DB::transaction(function () use ($id) {
            // Fetch the booking 
            $booking = EventRegistration::where('id', $id)->lockForUpdate()->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.',
                ], 404);
            }

            $event = $booking->event;

            //  handle if event has already started
            if (Carbon::parse($event->start_at)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a booking for an event that has already started.',
                ], 422);
            }

            // reset   seats back to event capacity (only if booking was confirmed)
            if ($booking->status === 'CONFIRMED') {
                $event->increment('available_seats', $booking->quantity);
            }

            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully and seats restored.',
                'data' => [
                    'restored_seats' => $booking->quantity,
                    'available_seats' => $event->fresh()->available_seats,
                ],
            ], 200);
        });

    }

}
