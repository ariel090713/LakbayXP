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
            ->with(['place', 'organizer']);

        if ($request->filled('category')) {
            $category = PlaceCategory::tryFrom($request->input('category'));
            if ($category) {
                $query->where('category', $category);
            }
        }

        if ($request->filled('place')) {
            $query->where('place_id', $request->input('place'));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        $events = $query->orderBy('event_date')->paginate(15);

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

        $event->load(['place', 'organizer']);

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
