<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventPlace;
use App\Models\EventRule;
use App\Models\Place;
use App\Services\BookingService;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizerApiController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected BookingService $bookingService,
    ) {}

    /**
     * Dashboard stats.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalEvents = $user->organizedEvents()->count();
        $publishedEvents = $user->organizedEvents()->where('status', EventStatus::Published)->count();
        $pendingReview = $user->organizedEvents()->where('status', EventStatus::PendingReview)->count();
        $completedEvents = $user->organizedEvents()->where('status', EventStatus::Completed)->count();

        $totalBookings = \App\Models\Booking::whereHas('event', fn ($q) => $q->where('organizer_id', $user->id))->count();
        $pendingBookings = \App\Models\Booking::whereHas('event', fn ($q) => $q->where('organizer_id', $user->id))->where('status', BookingStatus::Pending)->count();

        $upcomingEvents = $user->organizedEvents()
            ->whereIn('status', [EventStatus::Published, EventStatus::Full])
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_events' => $totalEvents,
                'published_events' => $publishedEvents,
                'pending_review' => $pendingReview,
                'completed_events' => $completedEvents,
                'total_bookings' => $totalBookings,
                'pending_bookings' => $pendingBookings,
                'is_verified' => $user->is_verified_organizer,
            ],
            'upcoming_events' => $upcomingEvents,
        ]);
    }

    /**
     * List my events.
     */
    public function events(Request $request): JsonResponse
    {
        $query = $request->user()->organizedEvents()
            ->withCount(['bookings as pending_bookings_count' => fn ($q) => $q->where('status', BookingStatus::Pending)])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    /**
     * Show single event with bookings.
     */
    public function showEvent(Request $request, Event $event): JsonResponse
    {
        if ($event->organizer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $event->load(['place', 'itinerary.place', 'rules', 'photos', 'bookings.user:id,name,username,avatar_path']);

        return response()->json($event);
    }

    /**
     * Create event.
     */
    public function createEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:10240'],
            'event_date' => ['required', 'date', 'after:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'meeting_place' => ['nullable', 'string', 'max:255'],
            'meeting_time' => ['nullable', 'string', 'max:50'],
            'meeting_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'meeting_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'max_slots' => ['required', 'integer', 'min:1'],
            'difficulty' => ['nullable', 'string', 'in:easy,moderate,hard,extreme'],
            'auto_approve_bookings' => ['nullable', 'boolean'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'max:10240'],
            'itinerary' => ['nullable', 'array'],
            'rules' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('event-covers', $request->file('cover_image'));
        }

        $event = $this->eventService->create($request->user(), $validated);

        // Save itinerary
        if (!empty($validated['itinerary'])) {
            foreach ($validated['itinerary'] as $i => $stop) {
                EventPlace::create([
                    'event_id' => $event->id,
                    'place_id' => $stop['place_id'] ?? null,
                    'custom_place_name' => $stop['custom_name'] ?? null,
                    'custom_place_location' => $stop['custom_location'] ?? null,
                    'day_number' => $stop['day'] ?? 1,
                    'sort_order' => $i + 1,
                    'activity' => $stop['activity'] ?? null,
                    'time_slot' => $stop['time'] ?? null,
                    'notes' => $stop['notes'] ?? null,
                ]);
            }
            $firstPlaceId = $validated['itinerary'][0]['place_id'] ?? null;
            if ($firstPlaceId) {
                $place = Place::find($firstPlaceId);
                if ($place) $event->update(['place_id' => $place->id, 'category' => $place->category]);
            }
        }

        // Save rules
        if (!empty($validated['rules'])) {
            foreach ($validated['rules'] as $i => $rule) {
                if (empty($rule['type']) || empty($rule['content'])) continue;
                EventRule::create([
                    'event_id' => $event->id,
                    'rule_type' => $rule['type'],
                    'content' => $rule['content'],
                    'sort_order' => $i + 1,
                ]);
            }
        }

        // Save gallery
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $photo) {
                $path = Storage::disk('s3')->putFile('event-photos', $photo);
                EventPhoto::create(['event_id' => $event->id, 'uploaded_by' => $request->user()->id, 'photo_path' => $path]);
            }
        }

        $event->load(['itinerary.place', 'rules', 'photos']);
        return response()->json(['message' => 'Event created.', 'event' => $event], 201);
    }

    /**
     * Update event (draft only).
     */
    public function updateEvent(Request $request, Event $event): JsonResponse
    {
        if ($event->organizer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        if ($event->status !== EventStatus::Draft) {
            return response()->json(['message' => 'Only draft events can be edited.'], 422);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:events,slug,' . $event->id],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:10240'],
            'event_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'meeting_place' => ['nullable', 'string', 'max:255'],
            'meeting_time' => ['nullable', 'string', 'max:50'],
            'meeting_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'meeting_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'max_slots' => ['sometimes', 'integer', 'min:1'],
            'difficulty' => ['nullable', 'string', 'in:easy,moderate,hard,extreme'],
            'auto_approve_bookings' => ['nullable', 'boolean'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'max:10240'],
            'remove_photos' => ['nullable', 'array'],
            'itinerary' => ['nullable', 'array'],
            'rules' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image_path) Storage::disk('s3')->delete($event->cover_image_path);
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('event-covers', $request->file('cover_image'));
        }

        unset($validated['cover_image'], $validated['gallery'], $validated['remove_photos'], $validated['itinerary'], $validated['rules']);
        $this->eventService->update($event, $validated);

        // Replace itinerary
        if ($request->has('itinerary')) {
            $event->itinerary()->delete();
            foreach ($request->input('itinerary', []) as $i => $stop) {
                EventPlace::create([
                    'event_id' => $event->id,
                    'place_id' => $stop['place_id'] ?? null,
                    'custom_place_name' => $stop['custom_name'] ?? null,
                    'custom_place_location' => $stop['custom_location'] ?? null,
                    'day_number' => $stop['day'] ?? 1,
                    'sort_order' => $i + 1,
                    'activity' => $stop['activity'] ?? null,
                    'time_slot' => $stop['time'] ?? null,
                    'notes' => $stop['notes'] ?? null,
                ]);
            }
        }

        // Replace rules
        if ($request->has('rules')) {
            $event->rules()->delete();
            foreach ($request->input('rules', []) as $i => $rule) {
                if (empty($rule['type']) || empty($rule['content'])) continue;
                EventRule::create([
                    'event_id' => $event->id,
                    'rule_type' => $rule['type'],
                    'content' => $rule['content'],
                    'sort_order' => $i + 1,
                ]);
            }
        }

        // Remove photos
        if ($request->has('remove_photos')) {
            $photos = EventPhoto::where('event_id', $event->id)->whereIn('id', $request->input('remove_photos'))->get();
            foreach ($photos as $p) { Storage::disk('s3')->delete($p->photo_path); $p->delete(); }
        }

        // Add gallery
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $photo) {
                $path = Storage::disk('s3')->putFile('event-photos', $photo);
                EventPhoto::create(['event_id' => $event->id, 'uploaded_by' => $request->user()->id, 'photo_path' => $path]);
            }
        }

        $event->load(['itinerary.place', 'rules', 'photos']);
        return response()->json(['message' => 'Event updated.', 'event' => $event]);
    }

    /**
     * Submit for review.
     */
    public function publishEvent(Request $request, Event $event): JsonResponse
    {
        try {
            $this->eventService->publish($request->user(), $event);
            return response()->json(['message' => 'Event submitted for review.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel event.
     */
    public function cancelEvent(Request $request, Event $event): JsonResponse
    {
        try {
            $this->eventService->cancel($request->user(), $event);
            return response()->json(['message' => 'Event cancelled.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Complete event.
     */
    public function completeEvent(Request $request, Event $event): JsonResponse
    {
        try {
            $this->eventService->completeEvent($request->user(), $event);
            return response()->json(['message' => 'Event completed. Places unlocked for attendees.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Approve booking.
     */
    public function approveBooking(Request $request, \App\Models\Booking $booking): JsonResponse
    {
        try {
            $this->bookingService->approveBooking($request->user(), $booking);
            return response()->json(['message' => 'Booking approved.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reject booking.
     */
    public function rejectBooking(Request $request, \App\Models\Booking $booking): JsonResponse
    {
        try {
            $this->bookingService->rejectBooking($request->user(), $booking);
            return response()->json(['message' => 'Booking rejected.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Approve all pending bookings for an event.
     */
    public function approveAllBookings(Request $request, Event $event): JsonResponse
    {
        if ($event->organizer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $pending = $event->bookings()->where('status', BookingStatus::Pending)->get();
        $count = 0;
        foreach ($pending as $booking) {
            try { $this->bookingService->approveBooking($request->user(), $booking); $count++; }
            catch (\Throwable $e) { break; }
        }

        return response()->json(['message' => "Approved {$count} bookings."]);
    }

    /**
     * List places (for itinerary picker).
     */
    public function places(): JsonResponse
    {
        $places = Place::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug', 'category']);
        return response()->json(['data' => $places]);
    }
}
