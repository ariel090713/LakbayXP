<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEventController extends Controller
{
    public function __construct(protected EventService $eventService) {}

    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending_review');

        $query = Event::with(['organizer', 'place'])->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $events = $query->paginate(20)->appends(['status' => $status]);

        $counts = [
            'pending_review' => Event::where('status', EventStatus::PendingReview)->count(),
            'published' => Event::where('status', EventStatus::Published)->count(),
            'all' => Event::count(),
        ];

        return view('admin.events.index', compact('events', 'status', 'counts'));
    }

    public function show(Event $event): View
    {
        $event->load(['organizer', 'place', 'itinerary.place', 'rules', 'bookings.user']);

        return view('admin.events.show', compact('event'));
    }

    public function approve(Event $event): RedirectResponse
    {
        $this->eventService->approveEvent($event);
        return redirect()->route('admin.events.index')->with('success', "Event \"{$event->title}\" approved and published.");
    }

    public function reject(Event $event): RedirectResponse
    {
        if ($event->status !== EventStatus::PendingReview) {
            return redirect()->route('admin.events.index')->with('error', 'Only pending events can be rejected.');
        }

        $event->update(['status' => EventStatus::Draft]);
        return redirect()->route('admin.events.index')->with('success', "Event \"{$event->title}\" rejected and returned to draft.");
    }
}
