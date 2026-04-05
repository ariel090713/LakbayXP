<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $eventIds = $user->organizedEvents()->pluck('id');

        return view('organizer.dashboard', [
            'totalEvents' => $user->organizedEvents()->count(),
            'publishedEvents' => $user->organizedEvents()->where('status', EventStatus::Published)->count(),
            'totalBookings' => Booking::whereIn('event_id', $eventIds)->count(),
            'pendingBookings' => Booking::whereIn('event_id', $eventIds)->where('status', BookingStatus::Pending)->count(),
        ]);
    }
}
