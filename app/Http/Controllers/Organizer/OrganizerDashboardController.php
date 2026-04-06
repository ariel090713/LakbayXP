<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerDashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Redirect to onboarding if not completed
        if (!$user->onboarding_completed) {
            return redirect()->route('organizer.onboarding');
        }

        $eventIds = $user->organizedEvents()->pluck('id');

        $recentEvents = $user->organizedEvents()
            ->with('place')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $upcomingEvents = $user->organizedEvents()
            ->with('place')
            ->whereIn('status', [EventStatus::Published, EventStatus::Full])
            ->where('event_date', '>=', now()->startOfDay())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        $recentBookings = Booking::whereIn('event_id', $eventIds)
            ->with(['user', 'event'])
            ->where('status', BookingStatus::Pending)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $pendingReviewCount = $user->organizedEvents()->where('status', EventStatus::PendingReview)->count();

        return view('organizer.dashboard', [
            'user' => $user,
            'totalEvents' => $user->organizedEvents()->count(),
            'publishedEvents' => $user->organizedEvents()->where('status', EventStatus::Published)->count(),
            'pendingReviewEvents' => $pendingReviewCount,
            'completedEvents' => $user->organizedEvents()->where('status', EventStatus::Completed)->count(),
            'totalBookings' => Booking::whereIn('event_id', $eventIds)->count(),
            'approvedBookings' => Booking::whereIn('event_id', $eventIds)->where('status', BookingStatus::Approved)->count(),
            'pendingBookings' => Booking::whereIn('event_id', $eventIds)->where('status', BookingStatus::Pending)->count(),
            'recentEvents' => $recentEvents,
            'upcomingEvents' => $upcomingEvents,
            'recentBookings' => $recentBookings,
        ]);
    }
}
