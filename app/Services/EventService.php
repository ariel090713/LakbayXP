<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\UnlockMethod;
use App\Models\Event;
use App\Models\PlaceUnlock;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function __construct(
        protected UnlockService $unlockService,
        protected AchievementService $achievementService,
    ) {}
    /**
     * Create a new event with draft status.
     */
    public function create(User $organizer, array $data): Event
    {
        $data['organizer_id'] = $organizer->id;
        $data['status'] = EventStatus::Draft;

        return Event::create($data);
    }

    /**
     * Update an existing event.
     */
    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->refresh();
    }

    /**
     * Publish an event (draft → published).
     */
    public function publish(User $organizer, Event $event): Event
    {
        if ($organizer->id !== $event->organizer_id) {
            throw new AuthorizationException('You are not the organizer of this event.');
        }

        if ($event->status !== EventStatus::Draft) {
            throw new \InvalidArgumentException('Only draft events can be published.');
        }

        $event->update(['status' => EventStatus::Published]);

        return $event->refresh();
    }

    /**
     * Cancel an event.
     */
    public function cancel(User $organizer, Event $event): Event
    {
        if ($organizer->id !== $event->organizer_id) {
            throw new AuthorizationException('You are not the organizer of this event.');
        }

        $event->update(['status' => EventStatus::Cancelled]);

        return $event->refresh();
    }

    /**
     * Complete an event and auto-unlock the place for all approved attendees.
     */
    public function completeEvent(User $organizer, Event $event): Event
    {
        if ($organizer->id !== $event->organizer_id) {
            throw new AuthorizationException('You are not the organizer of this event.');
        }

        if (!in_array($event->status, [EventStatus::Published, EventStatus::Full])) {
            throw new \InvalidArgumentException('Only published or full events can be completed.');
        }

        if ($event->event_date->isFuture()) {
            throw new \InvalidArgumentException('Cannot complete an event with a future date.');
        }

        DB::transaction(function () use ($event) {
            $event->update(['status' => EventStatus::Completed]);

            $approvedBookings = $event->bookings()
                ->where('status', BookingStatus::Approved)
                ->with('user')
                ->get();

            foreach ($approvedBookings as $booking) {
                $alreadyUnlocked = PlaceUnlock::where('user_id', $booking->user_id)
                    ->where('place_id', $event->place_id)
                    ->exists();

                if (!$alreadyUnlocked) {
                    $this->unlockService->unlockPlace(
                        user: $booking->user,
                        place: $event->place,
                        method: UnlockMethod::EventCompletion,
                        event: $event,
                    );
                }
            }
        });

        return $event->fresh();
    }
}
