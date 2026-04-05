<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Booking;
use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationService
{
    protected ?Messaging $messaging;

    public function __construct(?Messaging $messaging = null)
    {
        $this->messaging = $messaging;
    }

    /**
     * Send a push notification to a user via FCM.
     * Gracefully skips if the user has no FCM token or messaging is unavailable.
     */
    public function sendPush(User $user, string $title, string $body, array $data = []): void
    {
        if (!$user->fcm_token || !$this->messaging) {
            return;
        }

        $message = CloudMessage::withTarget('token', $user->fcm_token)
            ->withNotification(Notification::create($title, $body))
            ->withData(array_map('strval', $data));

        $this->messaging->send($message);
    }

    /**
     * Notify the event organizer that a new booking was created.
     */
    public function notifyBookingCreated(Booking $booking): void
    {
        $booking->loadMissing(['event.organizer', 'user']);

        $this->sendPush(
            $booking->event->organizer,
            'New Booking',
            "{$booking->user->username} booked {$booking->event->title}",
            ['type' => 'booking_created', 'booking_id' => $booking->id]
        );
    }

    /**
     * Notify the booking user that their booking status changed.
     */
    public function notifyBookingStatusChanged(Booking $booking): void
    {
        $booking->loadMissing(['event', 'user']);

        $this->sendPush(
            $booking->user,
            'Booking ' . ucfirst($booking->status->value),
            "Your booking for {$booking->event->title} was {$booking->status->value}",
            ['type' => 'booking_status', 'booking_id' => $booking->id]
        );
    }

    /**
     * Notify a user that they earned a badge.
     */
    public function notifyBadgeAwarded(User $user, Badge $badge): void
    {
        $this->sendPush(
            $user,
            'Badge Unlocked!',
            "You earned the {$badge->name} badge",
            ['type' => 'badge_awarded', 'badge_id' => $badge->id]
        );
    }
}
