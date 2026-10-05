<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {

        return view('admin.dashboard');

    }

    public function index()
    {
        return view('admin.categories');
    }

    public function getReport()
    {

        // get users(attendee)'s count
        $totalUsers = User::whereHas('role', function ($query) {
            $query->where('name', 'attendee');
        })->count();
        // events
        $totalEvents = Event::count();

        $totalBookings = EventRegistration::sum('quantity');

        // revenue
        $totalRevenue = EventRegistration::sum('total_price');

        $data = [
            'total_users' => $totalUsers,
            'total_events' => $totalEvents,
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
        ];

        return response()->json($data);

    }
}
