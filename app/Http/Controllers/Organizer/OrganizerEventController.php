<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\PlaceCategory;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventPlace;
use App\Models\EventRule;
use App\Models\Place;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            ->withCount(['bookings as pending_bookings_count' => function ($q) {
                $q->where('status', \App\Enums\BookingStatus::Pending);
            }])
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
            'place_id' => ['nullable', 'exists:places,id'],
            'category' => ['nullable', 'string'],
            'event_date' => ['required', 'date', 'after:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:10240'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'max:10240'],
            'meeting_place' => ['nullable', 'string', 'max:255'],
            'meeting_time' => ['nullable', 'string', 'max:50'],
            'meeting_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'meeting_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'max_slots' => ['required', 'integer', 'min:1'],
            'auto_approve_bookings' => ['nullable', 'boolean'],
            'difficulty' => ['nullable', 'string', 'in:easy,moderate,hard,extreme'],
            // Itinerary
            'itinerary_place_ids' => ['nullable', 'array'],
            'itinerary_days' => ['nullable', 'array'],
            'itinerary_activities' => ['nullable', 'array'],
            'itinerary_times' => ['nullable', 'array'],
            'itinerary_notes' => ['nullable', 'array'],
            // Rules & Instructions
            'rule_types' => ['nullable', 'array'],
            'rule_contents' => ['nullable', 'array'],
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('event-covers', $request->file('cover_image'));
        }

        $event = $this->eventService->create($request->user(), $validated);

        // Set place_id and category from first itinerary item if not provided
        $firstPlaceId = collect($request->input('itinerary_place_ids', []))->first();
        if (!$event->place_id && $firstPlaceId) {
            $firstPlace = Place::find($firstPlaceId);
            if ($firstPlace) {
                $event->update([
                    'place_id' => $firstPlace->id,
                    'category' => $firstPlace->category,
                ]);
            }
        }

        // Save itinerary
        if ($request->has('itinerary_place_ids')) {
            foreach ($request->input('itinerary_place_ids', []) as $i => $placeId) {
                $customName = $request->input("itinerary_custom_names.{$i}");
                if (!$placeId && !$customName) continue;

                EventPlace::create([
                    'event_id' => $event->id,
                    'place_id' => $placeId ?: null,
                    'custom_place_name' => $placeId ? null : $customName,
                    'custom_place_location' => $placeId ? null : $request->input("itinerary_custom_locations.{$i}"),
                    'day_number' => $request->input("itinerary_days.{$i}", 1),
                    'sort_order' => $i + 1,
                    'activity' => $request->input("itinerary_activities.{$i}"),
                    'time_slot' => $request->input("itinerary_times.{$i}"),
                    'notes' => $request->input("itinerary_notes.{$i}"),
                ]);
            }
        }

        // Save rules & instructions
        if ($request->has('rule_types')) {
            foreach ($request->input('rule_types', []) as $i => $type) {
                $content = $request->input("rule_contents.{$i}");
                if (!$type || !$content) continue;
                EventRule::create([
                    'event_id' => $event->id,
                    'rule_type' => $type,
                    'content' => $content,
                    'sort_order' => $i + 1,
                ]);
            }
        }

        // Save gallery photos
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $photo) {
                $path = Storage::disk('s3')->putFile('event-photos', $photo);
                EventPhoto::create([
                    'event_id' => $event->id,
                    'uploaded_by' => $request->user()->id,
                    'photo_path' => $path,
                ]);
            }
        }

        return redirect()->route('organizer.events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified event with bookings.
     */
    public function show(Event $event): View
    {
        $this->authorizeOrganizer($event);

        $event->load(['place', 'bookings.user', 'itinerary.place', 'rules', 'photos']);

        return view('organizer.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event): View
    {
        $this->authorizeOrganizer($event);

        // Only draft events can be edited
        if ($event->status !== \App\Enums\EventStatus::Draft) {
            return redirect()->route('organizer.events.show', $event)
                ->with('error', 'Only draft events can be edited.');
        }

        $places = Place::where('is_active', true)->orderBy('name')->get();
        $event->load(['itinerary.place', 'rules', 'photos']);

        $itineraryData = $event->itinerary->map(function ($s) {
            return [
                'place_id' => $s->place_id,
                'custom_name' => $s->custom_place_name,
                'custom_location' => $s->custom_place_location,
                'day' => $s->day_number,
                'time' => $s->time_slot,
                'activity' => $s->activity,
                'notes' => $s->notes,
            ];
        });

        $rulesData = $event->rules->map(function ($r) {
            return ['type' => $r->rule_type, 'content' => $r->content];
        });

        return view('organizer.events.edit', compact('event', 'places', 'itineraryData', 'rulesData'));
    }

    /**
     * Update the specified event (draft only).
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeOrganizer($event);

        if ($event->status !== \App\Enums\EventStatus::Draft) {
            return redirect()->route('organizer.events.show', $event)
                ->with('error', 'Only draft events can be edited.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug,' . $event->id],
            'place_id' => ['nullable', 'exists:places,id'],
            'category' => ['nullable', 'string'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:10240'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'max:10240'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer', 'exists:event_photos,id'],
            'meeting_place' => ['nullable', 'string', 'max:255'],
            'meeting_time' => ['nullable', 'string', 'max:50'],
            'meeting_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'meeting_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'max_slots' => ['required', 'integer', 'min:1'],
            'auto_approve_bookings' => ['nullable', 'boolean'],
            'difficulty' => ['nullable', 'string'],
            'itinerary_place_ids' => ['nullable', 'array'],
            'itinerary_custom_names' => ['nullable', 'array'],
            'itinerary_custom_locations' => ['nullable', 'array'],
            'itinerary_days' => ['nullable', 'array'],
            'itinerary_activities' => ['nullable', 'array'],
            'itinerary_times' => ['nullable', 'array'],
            'itinerary_notes' => ['nullable', 'array'],
            'rule_types' => ['nullable', 'array'],
            'rule_contents' => ['nullable', 'array'],
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover
            if ($event->cover_image_path) {
                Storage::disk('s3')->delete($event->cover_image_path);
            }
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('event-covers', $request->file('cover_image'));
        }

        unset($validated['cover_image'], $validated['gallery'], $validated['remove_photos'],
              $validated['itinerary_place_ids'], $validated['itinerary_custom_names'], $validated['itinerary_custom_locations'],
              $validated['itinerary_days'], $validated['itinerary_activities'], $validated['itinerary_times'], $validated['itinerary_notes'],
              $validated['rule_types'], $validated['rule_contents']);

        $this->eventService->update($event, $validated);

        // Replace itinerary
        $event->itinerary()->delete();
        if ($request->has('itinerary_place_ids')) {
            foreach ($request->input('itinerary_place_ids', []) as $i => $placeId) {
                $customName = $request->input("itinerary_custom_names.{$i}");
                if (!$placeId && !$customName) continue;
                EventPlace::create([
                    'event_id' => $event->id,
                    'place_id' => $placeId ?: null,
                    'custom_place_name' => $placeId ? null : $customName,
                    'custom_place_location' => $placeId ? null : $request->input("itinerary_custom_locations.{$i}"),
                    'day_number' => $request->input("itinerary_days.{$i}", 1),
                    'sort_order' => $i + 1,
                    'activity' => $request->input("itinerary_activities.{$i}"),
                    'time_slot' => $request->input("itinerary_times.{$i}"),
                    'notes' => $request->input("itinerary_notes.{$i}"),
                ]);
            }
        }

        // Set place_id from first itinerary item
        $firstPlaceId = collect($request->input('itinerary_place_ids', []))->first();
        if ($firstPlaceId) {
            $firstPlace = Place::find($firstPlaceId);
            if ($firstPlace) {
                $event->update(['place_id' => $firstPlace->id, 'category' => $firstPlace->category]);
            }
        }

        // Replace rules
        $event->rules()->delete();
        if ($request->has('rule_types')) {
            foreach ($request->input('rule_types', []) as $i => $type) {
                $content = $request->input("rule_contents.{$i}");
                if (!$type || !$content) continue;
                EventRule::create([
                    'event_id' => $event->id,
                    'rule_type' => $type,
                    'content' => $content,
                    'sort_order' => $i + 1,
                ]);
            }
        }

        // Remove selected gallery photos
        if ($request->has('remove_photos')) {
            $photosToRemove = EventPhoto::where('event_id', $event->id)
                ->whereIn('id', $request->input('remove_photos'))
                ->get();
            foreach ($photosToRemove as $photo) {
                Storage::disk('s3')->delete($photo->photo_path);
                $photo->delete();
            }
        }

        // Add new gallery photos
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $photo) {
                $path = Storage::disk('s3')->putFile('event-photos', $photo);
                EventPhoto::create([
                    'event_id' => $event->id,
                    'uploaded_by' => $request->user()->id,
                    'photo_path' => $path,
                ]);
            }
        }

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Cancel an event.
     */
    public function cancel(Request $request, Event $event): RedirectResponse
    {
        $this->eventService->cancel($request->user(), $event);

        return redirect()->route('organizer.events.index')
            ->with('success', 'Event cancelled.');
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

