<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Badge;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Place;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
     * Core: save notification to DB + send FCM push.
     */
    public function notify(User $user, string $type, string $title, string $body, array $data = []): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        $this->sendPush($user, $title, $body, array_merge($data, ['type' => $type]));

        return $notification;
    }

    /**
     * Send FCM push notification.
     */
    public function sendPush(User $user, string $title, string $body, array $data = []): void
    {
        if (!$user->fcm_token || !$this->messaging) return;

        try {
            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withData(array_map('strval', $data));

            $this->messaging->send($message);
        } catch (\Throwable $e) {
            Log::warning("FCM push failed for user {$user->id}", ['error' => $e->getMessage()]);
        }
    }

    // ── Badge Earned ──
    public function notifyBadgeAwarded(User $user, Badge $badge): void
    {
        $this->notify($user, 'badge_earned', '🏅 Badge Unlocked!', "You earned the \"{$badge->name}\" badge! " . ($badge->points > 0 ? "+{$badge->points} points" : ''), [
            'badge_id' => $badge->id,
            'badge_name' => $badge->name,
            'points' => $badge->points ?? 0,
        ]);
    }

    // ── Level Up ──
    public function notifyLevelUp(User $user, int $oldLevel, int $newLevel): void
    {
        $this->notify($user, 'level_up', '⚡ Level Up!', "You reached Level {$newLevel}!", [
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
        ]);
    }

    // ── Place Unlocked ──
    public function notifyPlaceUnlocked(User $user, Place $place, int $xpEarned): void
    {
        $this->notify($user, 'place_unlocked', '🔓 Place Unlocked!', "You unlocked {$place->name}! +{$xpEarned} XP", [
            'place_id' => $place->id,
            'place_name' => $place->name,
            'xp_earned' => $xpEarned,
        ]);
    }

    // ── Booking Approved ──
    public function notifyBookingApproved(Booking $booking): void
    {
        $booking->loadMissing(['event', 'user']);
        $this->notify($booking->user, 'booking_approved', '🎫 Booking Confirmed!', "You're in! {$booking->event->title} on {$booking->event->event_date->format('M d, Y')}", [
            'booking_id' => $booking->id,
            'event_id' => $booking->event_id,
            'event_title' => $booking->event->title,
        ]);
    }

    // ── Booking Rejected ──
    public function notifyBookingRejected(Booking $booking): void
    {
        $booking->loadMissing(['event', 'user']);
        $this->notify($booking->user, 'booking_rejected', 'Booking Update', "Your booking for {$booking->event->title} was not approved.", [
            'booking_id' => $booking->id,
            'event_id' => $booking->event_id,
        ]);
    }

    // ── Event Completed ──
    public function notifyEventCompleted(User $user, Event $event): void
    {
        $this->notify($user, 'event_completed', '🏆 Adventure Complete!', "You completed {$event->title}!", [
            'event_id' => $event->id,
            'event_title' => $event->title,
        ]);
    }

    // ── New Follower ──
    public function notifyNewFollower(User $user, User $follower): void
    {
        $this->notify($user, 'follow', '👤 New Follower', "{$follower->name} started following you.", [
            'follower_id' => $follower->id,
            'follower_name' => $follower->name,
            'follower_username' => $follower->username,
        ]);
    }

    // ── Comment on Post ──
    public function notifyComment(User $postOwner, User $commenter, Post $post): void
    {
        if ($postOwner->id === $commenter->id) return; // don't notify self
        $this->notify($postOwner, 'comment', '💬 New Comment', "{$commenter->name} commented on your post.", [
            'post_id' => $post->id,
            'commenter_id' => $commenter->id,
            'commenter_name' => $commenter->name,
        ]);
    }

    // ── Reaction on Post ──
    public function notifyReaction(User $postOwner, User $reactor, Post $post, string $reactionType): void
    {
        if ($postOwner->id === $reactor->id) return;
        $emoji = match($reactionType) { 'like'=>'👍', 'love'=>'❤️', 'fire'=>'🔥', 'wow'=>'😮', 'congrats'=>'🎉', default=>'👍' };
        $this->notify($postOwner, 'reaction', "{$emoji} New Reaction", "{$reactor->name} reacted to your post.", [
            'post_id' => $post->id,
            'reactor_id' => $reactor->id,
            'reaction_type' => $reactionType,
        ]);
    }

    // ── XP Granted by Admin ──
    public function notifyXpGranted(User $user, int $amount, string $description): void
    {
        $this->notify($user, 'xp_earned', '⚡ XP Bonus!', "+{$amount} XP: {$description}", [
            'amount' => $amount,
        ]);
    }

    // ── Booking Created (for organizer) ──
    public function notifyBookingCreated(Booking $booking): void
    {
        $booking->loadMissing(['event.organizer', 'user']);
        $this->notify($booking->event->organizer, 'booking_created', '🎫 New Booking', "{$booking->user->name} booked {$booking->event->title}", [
            'booking_id' => $booking->id,
            'event_id' => $booking->event_id,
        ]);
    }

    // ── Legacy compatibility ──
    public function notifyBookingStatusChanged(Booking $booking): void
    {
        if ($booking->status->value === 'approved') {
            $this->notifyBookingApproved($booking);
        } elseif ($booking->status->value === 'rejected') {
            $this->notifyBookingRejected($booking);
        }
    }
}
