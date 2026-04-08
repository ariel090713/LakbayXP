<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Exceptions\NoSlotsAvailableException;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    /**
     * Book an event for a user.
     *
     * Uses DB::transaction with lockForUpdate() for race condition prevention.
     */
    public function bookEvent(User $user, Event $event): Booking
    {
        if ($event->status !== EventStatus::Published) {
            throw new \InvalidArgumentException('Only published events can be booked.');
        }

        if ($event->event_date->isPast()) {
            throw new \InvalidArgumentException('Cannot book an event with a past date.');
        }

        // Check for existing booking
        $existingBooking = Booking::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingBooking) {
            if (in_array($existingBooking->status, [BookingStatus::Pending, BookingStatus::Approved])) {
                throw new \InvalidArgumentException('You already have an active booking for this event.');
            }

            // Re-book: cancelled or rejected booking — reuse the row
            $status = $event->auto_approve_bookings
                ? BookingStatus::Approved
                : BookingStatus::Pending;

            $existingBooking->update([
                'status' => $status,
                'approved_at' => $status === BookingStatus::Approved ? now() : null,
                'rejected_at' => null,
            ]);

            // Check if event is now full
            $available = $event->max_slots - $event->bookings()
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
                ->count();
            if ($available <= 0) {
                $event->update(['status' => EventStatus::Full]);
            }

            $this->notificationService->notifyBookingCreated($existingBooking);

            return $existingBooking;
        }

        $booking = null;

        DB::transaction(function () use ($user, $event, &$booking) {
            // Lock event row to prevent race conditions on slot count
            $event = Event::lockForUpdate()->find($event->id);

            $availableSlots = $event->max_slots - $event->bookings()
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
                ->count();

            if ($availableSlots <= 0) {
                throw new NoSlotsAvailableException();
            }

            $status = $event->auto_approve_bookings
                ? BookingStatus::Approved
                : BookingStatus::Pending;

            $booking = Booking::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => $status,
                'approved_at' => $status === BookingStatus::Approved ? now() : null,
            ]);

            // Recount after insert
            $newAvailable = $event->max_slots - $event->bookings()
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
                ->count();

            if ($newAvailable <= 0) {
                $event->update(['status' => EventStatus::Full]);
            }
        });

        $this->notificationService->notifyBookingCreated($booking);

        return $booking;
    }

    /**
     * Approve a pending booking.
     */
    public function approveBooking(User $organizer, Booking $booking): Booking
    {
        $booking->load('event');

        if ($organizer->id !== $booking->event->organizer_id) {
            throw new AuthorizationException('You are not the organizer of this event.');
        }

        if ($booking->status !== BookingStatus::Pending) {
            throw new \InvalidArgumentException('Only pending bookings can be approved.');
        }

        $booking->update([
            'status' => BookingStatus::Approved,
            'approved_at' => now(),
        ]);

        // Check if event is now full
        if ($booking->event->availableSlots() <= 0) {
            $booking->event->update(['status' => EventStatus::Full]);
        }

        $booking->refresh();

        $this->notificationService->notifyBookingStatusChanged($booking);

        return $booking;
    }

    /**
     * Reject a pending booking.
     */
    public function rejectBooking(User $organizer, Booking $booking): Booking
    {
        $booking->load('event');

        if ($organizer->id !== $booking->event->organizer_id) {
            throw new AuthorizationException('You are not the organizer of this event.');
        }

        if ($booking->status !== BookingStatus::Pending) {
            throw new \InvalidArgumentException('Only pending bookings can be rejected.');
        }

        $booking->update([
            'status' => BookingStatus::Rejected,
            'rejected_at' => now(),
        ]);

        $booking->refresh();

        $this->notificationService->notifyBookingStatusChanged($booking);

        return $booking;
    }

    /**
     * Cancel a booking (by the booking user).
     */
    public function cancelBooking(User $user, Booking $booking): Booking
    {
        if ($user->id !== $booking->user_id) {
            throw new AuthorizationException('You can only cancel your own bookings.');
        }

        if (!in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved])) {
            throw new \InvalidArgumentException('Only pending or approved bookings can be cancelled.');
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
        ]);

        return $booking->refresh();
    }
}
