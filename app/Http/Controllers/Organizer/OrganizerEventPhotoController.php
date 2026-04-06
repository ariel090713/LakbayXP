<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizerEventPhotoController extends Controller
{
    /**
     * Store a new event photo.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        if ($event->organizer_id !== $request->user()->id) {
            abort(403, 'You do not own this event.');
        }

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $path = Storage::disk()->putFile('event-photos', $request->file('photo'));

        EventPhoto::create([
            'event_id' => $event->id,
            'uploaded_by' => $request->user()->id,
            'photo_path' => $path,
            'caption' => $validated['caption'] ?? null,
        ]);

        return redirect()->route('organizer.events.show', $event)
            ->with('success', 'Photo uploaded successfully.');
    }
}
