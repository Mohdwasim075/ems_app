<?php

namespace App\Http\Controllers;

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

        $totalBookings = EventRegistration::all()
            ->sum('quantity');

        // revenue
        $totalRevenue = EventRegistration::all()->sum('total_price');

        $data = [
            'total_users' => $totalUsers,
            'total_events' => $totalEvents,
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
        ];

        return response()->json($data);

    }

   

   

    


}
