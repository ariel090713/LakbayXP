<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\UnlockMethod;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Place;
use App\Models\PlaceUnlock;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UnlockService
{
    public function __construct(
        protected AchievementService $achievementService,
        protected XpService $xpService,
        protected NotificationService $notificationService,
    ) {}

    /**
     * Unlock a place for a user.
     *
     * Validates: place is active, no duplicate unlock, method-specific preconditions.
     * After unlock: recalculate level and check badges via AchievementService.
     */
    public function unlockPlace(
        User $user,
        Place $place,
        UnlockMethod $method,
        ?string $proofPhotoPath = null,
        ?Event $event = null,
        ?User $verifier = null,
    ): PlaceUnlock {
        // Place must be active
        if (!$place->is_active) {
            throw new \InvalidArgumentException('Cannot unlock an inactive place.');
        }

        // No duplicate unlock
        $alreadyUnlocked = PlaceUnlock::where('user_id', $user->id)
            ->where('place_id', $place->id)
            ->exists();

        if ($alreadyUnlocked) {
            throw new \InvalidArgumentException('You have already unlocked this place.');
        }

        // Method-specific preconditions
        $this->validateMethodPreconditions($user, $place, $method, $event, $verifier);

        // Store proof photo on S3 if provided
        $storedPhotoPath = null;
        if ($proofPhotoPath !== null) {
            $storedPhotoPath = Storage::disk()->putFile('proof-photos', $proofPhotoPath);
        }

        $unlock = PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => $method,
            'proof_photo_path' => $storedPhotoPath ?? $proofPhotoPath,
            'verified_by' => $verifier?->id,
            'event_id' => $event?->id,
            'verified_at' => $verifier ? now() : null,
        ]);

        // Award XP from place (for leveling)
        $xpResult = ['leveled_up' => false];
        if ($place->xp_reward > 0) {
            $xpResult = $this->xpService->awardPlaceXp($user, $place, $event);
        }

        // Notify place unlocked
        $this->notificationService->notifyPlaceUnlocked($user, $place, $place->xp_reward ?? 0);

        // Notify level up
        if ($xpResult['leveled_up'] ?? false) {
            $this->notificationService->notifyLevelUp($user, $xpResult['old_level'], $xpResult['new_level']);
        }

        // Trigger achievement recalculation (badges give points for rewards)
        $this->achievementService->checkAndAwardBadges($user);

        return $unlock;
    }

    /**
     * Validate method-specific preconditions.
     */
    private function validateMethodPreconditions(
        User $user,
        Place $place,
        UnlockMethod $method,
        ?Event $event,
        ?User $verifier,
    ): void {
        match ($method) {
            UnlockMethod::EventCompletion => $this->validateEventCompletion($user, $event),
            UnlockMethod::OrganizerVerification => $this->validateOrganizerVerification($verifier, $event),
            UnlockMethod::AdminApproval => $this->validateAdminApproval($verifier),
            default => null, // photo_proof, self_report, qr_code have no extra preconditions
        };
    }

    private function validateEventCompletion(User $user, ?Event $event): void
    {
        if ($event === null) {
            throw new \InvalidArgumentException('Event is required for event_completion unlock method.');
        }

        if ($event->status !== EventStatus::Completed) {
            throw new \InvalidArgumentException('Event must be completed for event_completion unlock.');
        }

        $hasApprovedBooking = $event->bookings()
            ->where('user_id', $user->id)
            ->where('status', BookingStatus::Approved)
            ->exists();

        if (!$hasApprovedBooking) {
            throw new \InvalidArgumentException('User must have an approved booking for this event.');
        }
    }

    private function validateOrganizerVerification(?User $verifier, ?Event $event): void
    {
        if ($verifier === null) {
            throw new \InvalidArgumentException('Verifier is required for organizer_verification unlock method.');
        }

        if ($event !== null && $verifier->id !== $event->organizer_id) {
            throw new \InvalidArgumentException('Verifier must be the event organizer.');
        }
    }

    private function validateAdminApproval(?User $verifier): void
    {
        if ($verifier === null) {
            throw new \InvalidArgumentException('Verifier is required for admin_approval unlock method.');
        }

        if ($verifier->role !== UserRole::Admin) {
            throw new \InvalidArgumentException('Verifier must have admin role for admin_approval.');
        }
    }
}
