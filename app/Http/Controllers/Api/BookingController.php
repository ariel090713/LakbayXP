<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
    ) {}

    /**
     * Book an event.
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        try {
            $booking = $this->bookingService->bookEvent($request->user(), $event);

            return response()->json([
                'message' => 'Booking created successfully.',
                'booking' => $booking->load('event'),
            ], 201);
        } catch (\App\Exceptions\NoSlotsAvailableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel own booking.
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        try {
            $booking = $this->bookingService->cancelBooking($request->user(), $booking);

            return response()->json([
                'message' => 'Booking cancelled successfully.',
                'booking' => $booking,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Approve a booking (organizer only).
     */
    public function approve(Request $request, Booking $booking): JsonResponse
    {
        try {
            $booking = $this->bookingService->approveBooking($request->user(), $booking);

            return response()->json([
                'message' => 'Booking approved successfully.',
                'booking' => $booking->load('event', 'user'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject a booking (organizer only).
     */
    public function reject(Request $request, Booking $booking): JsonResponse
    {
        try {
            $booking = $this->bookingService->rejectBooking($request->user(), $booking);

            return response()->json([
                'message' => 'Booking rejected successfully.',
                'booking' => $booking->load('event', 'user'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}

