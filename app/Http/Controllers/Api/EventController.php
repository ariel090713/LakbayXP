<?php

namespace App\Http\Controllers\Api;

use App\Enums\EventStatus;
use App\Enums\PlaceCategory;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
    ) {}
    /**
     * Return published/full events with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()
            ->whereIn('status', [EventStatus::Published, EventStatus::Full])
            ->with(['place', 'organizer:id,name,username,avatar_path,is_verified_organizer']);

        // Category
        if ($request->filled('category')) {
            $category = PlaceCategory::tryFrom($request->input('category'));
            if ($category) {
                $query->where('category', $category);
            }
        }

        // Place
        if ($request->filled('place')) {
            $query->where('place_id', $request->input('place'));
        }

        // Search
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('meeting_place', 'like', "%{$s}%");
            });
        }

        // Region (via place relationship)
        if ($request->filled('region')) {
            $region = $request->input('region');
            $query->whereHas('place', function ($q) use ($region) {
                $q->where('region', $region)
                  ->orWhere('region', 'like', "%{$region}%")
                  ->orWhereRaw('? LIKE CONCAT("%", region, "%")', [$region]);
            });
        }

        // Province (via place relationship)
        if ($request->filled('province')) {
            $province = $request->input('province');
            $query->whereHas('place', function ($q) use ($province) {
                $q->where('province', $province)
                  ->orWhere('province', 'like', "%{$province}%");
            });
        }

        // Difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->input('difficulty'));
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->where('event_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('event_date', '<=', $request->input('date_to'));
        }

        // Fee range
        if ($request->filled('fee_min')) {
            $query->where('fee', '>=', (float) $request->input('fee_min'));
        }
        if ($request->filled('fee_max')) {
            $query->where('fee', '<=', (float) $request->input('fee_max'));
        }

        // Has slots available
        if ($request->boolean('available_only')) {
            $query->where('status', EventStatus::Published);
        }

        // Sort
        $sort = $request->input('sort', 'date');
        match ($sort) {
            'date' => $query->orderBy('event_date'),
            'fee_low' => $query->orderBy('fee'),
            'fee_high' => $query->orderByDesc('fee'),
            'newest' => $query->orderByDesc('created_at'),
            'popular' => $query->withCount('bookings')->orderByDesc('bookings_count'),
            default => $query->orderBy('event_date'),
        };

        $events = $query->paginate($request->input('per_page', 15));

        return response()->json($events);
    }

    /**
     * Return a single event with place and organizer info.
     */
    public function show(Event $event): JsonResponse
    {
        if (!in_array($event->status, [EventStatus::Published, EventStatus::Full])) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        $event->load(['place.images', 'organizer', 'itinerary.place', 'rules']);

        return response()->json($event);
    }

    /**
     * Complete an event — auto-unlock place for approved attendees.
     */
    public function complete(Request $request, Event $event): JsonResponse
    {
        try {
            $event = $this->eventService->completeEvent($request->user(), $event);

            return response()->json([
                'message' => 'Event completed successfully.',
                'event' => $event->load('place'),
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
