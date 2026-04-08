<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerBookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
    ) {}

    /**
     * List bookings for an event.
     */
    public function index(Request $request, Event $event): View
    {
        $this->authorizeOrganizer($request, $event);

        $event->load(['bookings.user']);

        return view('organizer.bookings.index', compact('event'));
    }

    /**
     * Approve a pending booking.
     */
    public function approve(Request $request, Booking $booking): RedirectResponse
    {
        try {
            $this->bookingService->approveBooking($request->user(), $booking);

            return redirect()->route('organizer.bookings.index', $booking->event_id)
                ->with('success', 'Booking approved successfully.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\App\Exceptions\NoSlotsAvailableException $e) {
            return redirect()->route('organizer.bookings.index', $booking->event_id)
                ->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('organizer.bookings.index', $booking->event_id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a pending booking.
     */
    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        try {
            $this->bookingService->rejectBooking($request->user(), $booking);

            return redirect()->route('organizer.bookings.index', $booking->event_id)
                ->with('success', 'Booking rejected successfully.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('organizer.bookings.index', $booking->event_id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Approve all pending bookings for an event.
     */
    public function approveAll(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeOrganizer($request, $event);

        $pending = $event->bookings()->where('status', \App\Enums\BookingStatus::Pending)->get();
        $count = 0;

        foreach ($pending as $booking) {
            try {
                $this->bookingService->approveBooking($request->user(), $booking);
                $count++;
            } catch (\Throwable $e) {
                // skip if slots run out
                break;
            }
        }

        return redirect()->route('organizer.bookings.index', $event)
            ->with('success', "Approved {$count} bookings.");
    }

    /**
     * Ensure the authenticated user owns the event.
     */
    protected function authorizeOrganizer(Request $request, Event $event): void
    {
        if ($request->user()->id !== $event->organizer_id) {
            abort(403, 'You are not the organizer of this event.');
        }
    }
}
