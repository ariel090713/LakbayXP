<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\PlaceCategory;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Place;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class OrganizerEventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    /**
     * Display a listing of the organizer's events.
     */
    public function index(Request $request): View
    {
        $events = $request->user()
            ->organizedEvents()
            ->with('place')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('organizer.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        $places = Place::where('is_active', true)->orderBy('name')->get();
        $categories = PlaceCategory::cases();

        return view('organizer.events.create', compact('places', 'categories'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug'],
            'place_id' => ['required', 'exists:places,id'],
            'category' => ['required', new Enum(PlaceCategory::class)],
            'event_date' => ['required', 'date', 'after:today'],
            'description' => ['nullable', 'string'],
            'meeting_place' => ['nullable', 'string', 'max:255'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'max_slots' => ['required', 'integer', 'min:1'],
            'requirements' => ['nullable', 'array'],
            'auto_approve_bookings' => ['nullable', 'boolean'],
        ]);

        $this->eventService->create($request->user(), $validated);

        return redirect()->route('organizer.events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified event with bookings.
     */
    public function show(Event $event): View
    {
        $this->authorizeOrganizer($event);

        $event->load(['place', 'bookings.user']);

        return view('organizer.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event): View
    {
        $this->authorizeOrganizer($event);

        $places = Place::where('is_active', true)->orderBy('name')->get();
        $categories = PlaceCategory::cases();

        return view('organizer.events.edit', compact('event', 'places', 'categories'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeOrganizer($event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug,' . $event->id],
            'place_id' => ['required', 'exists:places,id'],
            'category' => ['required', new Enum(PlaceCategory::class)],
            'event_date' => ['required', 'date', 'after:today'],
            'description' => ['nullable', 'string'],
            'meeting_place' => ['nullable', 'string', 'max:255'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'max_slots' => ['required', 'integer', 'min:1'],
            'requirements' => ['nullable', 'array'],
            'auto_approve_bookings' => ['nullable', 'boolean'],
        ]);

        $this->eventService->update($event, $validated);

        return redirect()->route('organizer.events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Publish the event (draft → published).
     */
    public function publish(Request $request, Event $event): RedirectResponse
    {
        $this->eventService->publish($request->user(), $event);

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Event published successfully.');
    }

    /**
     * Complete the event — auto-unlock place for approved attendees.
     */
    public function complete(Request $request, Event $event): RedirectResponse
    {
        try {
            $this->eventService->completeEvent($request->user(), $event);

            return redirect()->route('organizer.events.show', $event)
                ->with('success', 'Event completed successfully. Places unlocked for all approved attendees.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('organizer.events.show', $event)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Ensure the authenticated user owns the event.
     */
    protected function authorizeOrganizer(Event $event): void
    {
        if (request()->user()->id !== $event->organizer_id) {
            abort(403, 'You are not the organizer of this event.');
        }
    }
}
