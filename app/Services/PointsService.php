<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Badge;
use App\Models\Event;
use App\Models\Place;
use App\Models\PointsHistory;
use App\Models\Reward;
use App\Models\User;

class PointsService
{
    /**
     * Award points to a user with history tracking.
     */
    public function awardPoints(
        User $user,
        int $amount,
        string $source,
        ?string $description = null,
        ?Badge $badge = null,
        ?Event $event = null,
        ?Reward $reward = null,
        ?User $grantedBy = null,
    ): PointsHistory {
        $user->increment('total_points', $amount);
        $user->increment('available_points', $amount);
        $user->refresh();

        return PointsHistory::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'source' => $source,
            'description' => $description ?? $this->autoDescription($source, $amount, $badge, $event, $reward),
            'badge_id' => $badge?->id,
            'event_id' => $event?->id,
            'reward_id' => $reward?->id,
            'granted_by' => $grantedBy?->id,
            'balance_after' => $user->total_points,
        ]);
    }

    /**
     * Spend points (for reward redemption).
     */
    public function spendPoints(User $user, int $amount, string $source, ?string $description = null, ?Reward $reward = null): PointsHistory
    {
        if ($user->available_points < $amount) {
            throw new \InvalidArgumentException('Not enough points.');
        }

        $user->decrement('available_points', $amount);
        $user->refresh();

        return PointsHistory::create([
            'user_id' => $user->id,
            'amount' => -$amount,
            'source' => $source,
            'description' => $description ?? "Redeemed: {$reward?->name}",
            'reward_id' => $reward?->id,
            'balance_after' => $user->total_points,
        ]);
    }

    /**
     * Award points from badge (handles repeatable badges).
     */
    public function awardBadgePoints(User $user, Badge $badge): void
    {
        if ($badge->points <= 0) return;
        $this->awardPoints($user, $badge->points, 'badge', "Badge: {$badge->name} (+{$badge->points} pts)", $badge);
    }

    /**
     * Award points from place unlock.
     */
    public function awardPlacePoints(User $user, Place $place): void
    {
        if (($place->points_reward ?? 0) <= 0) return;
        $this->awardPoints($user, $place->points_reward, 'place_unlock', "Unlocked: {$place->name} (+{$place->points_reward} pts)");
    }

    /**
     * Award points from event completion (based on difficulty setting).
     */
    public function awardEventPoints(User $user, Event $event): void
    {
        $difficulty = $event->difficulty ?? 'easy';
        $points = (int) AppSetting::get("points_event_{$difficulty}", 5);
        if ($points <= 0) return;
        $this->awardPoints($user, $points, 'event', "Completed: {$event->title} (+{$points} pts)", event: $event);
    }

    /**
     * Award points from level up.
     */
    public function awardLevelUpPoints(User $user, int $newLevel): void
    {
        $points = (int) AppSetting::get('points_per_level_up', 10);
        if ($points <= 0) return;
        $this->awardPoints($user, $points, 'level_up', "Reached Level {$newLevel} (+{$points} pts)");
    }

    /**
     * Admin grants points.
     */
    public function adminGrantPoints(User $admin, User $user, int $amount, string $description): PointsHistory
    {
        return $this->awardPoints($user, $amount, 'admin', $description, grantedBy: $admin);
    }

    /**
     * Get points history for a user.
     */
    public function getHistory(User $user, int $perPage = 15)
    {
        return PointsHistory::where('user_id', $user->id)
            ->with(['badge:id,name,slug', 'event:id,title,slug', 'reward:id,name,slug', 'grantedBy:id,name'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    private function autoDescription(string $source, int $amount, ?Badge $badge, ?Event $event, ?Reward $reward): string
    {
        return match ($source) {
            'badge' => "Badge: {$badge?->name} (+{$amount} pts)",
            'event' => "Event: {$event?->title} (+{$amount} pts)",
            'level_up' => "Level up (+{$amount} pts)",
            'place_unlock' => "Place unlock (+{$amount} pts)",
            'admin' => "Admin granted +{$amount} pts",
            'promo' => "Promo +{$amount} pts",
            'reward_redeem' => "Redeemed: {$reward?->name} (-{$amount} pts)",
            default => "+{$amount} pts",
        };
    }
}
