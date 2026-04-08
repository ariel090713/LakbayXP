<?php

namespace App\Services;

use App\Models\Place;
use App\Models\Badge;
use App\Models\Event;
use App\Models\User;
use App\Models\XpHistory;

class XpService
{
    const MAX_LEVEL = 100;

    /**
     * Award XP to a user with full history tracking.
     */
    public function awardXp(
        User $user,
        int $amount,
        string $source = 'other',
        ?string $category = null,
        ?string $description = null,
        ?Place $place = null,
        ?Badge $badge = null,
        ?Event $event = null,
        ?User $grantedBy = null,
    ): array {
        if ($amount <= 0) {
            return ['leveled_up' => false, 'new_level' => $user->level];
        }

        $oldLevel = $user->level;
        $newXp = $user->xp + $amount;
        $newLevel = $this->calculateLevel($newXp);

        $user->update([
            'xp' => $newXp,
            'level' => $newLevel,
        ]);

        // Log to history
        XpHistory::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'source' => $source,
            'category' => $category ?? $place?->category?->value,
            'description' => $description ?? $this->autoDescription($source, $amount, $place, $badge, $event),
            'place_id' => $place?->id,
            'badge_id' => $badge?->id,
            'event_id' => $event?->id,
            'granted_by' => $grantedBy?->id,
            'balance_after' => $newXp,
        ]);

        return [
            'leveled_up' => $newLevel > $oldLevel,
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'xp_gained' => $amount,
            'total_xp' => $newXp,
            'xp_for_next_level' => $this->xpRequiredForLevel($newLevel + 1),
            'xp_progress_in_level' => $newXp - $this->totalXpForLevel($newLevel),
        ];
    }

    /**
     * Admin grants XP to a user.
     */
    public function adminGrantXp(User $admin, User $user, int $amount, string $description, ?string $category = null): array
    {
        return $this->awardXp(
            user: $user,
            amount: $amount,
            source: 'admin',
            category: $category,
            description: $description,
            grantedBy: $admin,
        );
    }

    /**
     * Award XP from a place unlock.
     */
    public function awardPlaceXp(User $user, Place $place, ?Event $event = null): array
    {
        if ($place->xp_reward <= 0) return ['leveled_up' => false, 'new_level' => $user->level];

        return $this->awardXp(
            user: $user,
            amount: $place->xp_reward,
            source: 'place_unlock',
            category: $place->category?->value,
            place: $place,
            event: $event,
        );
    }

    /**
     * Award XP from a badge.
     */
    public function awardBadgeXp(User $user, Badge $badge): array
    {
        if (($badge->xp_reward ?? 0) <= 0) return ['leveled_up' => false, 'new_level' => $user->level];

        return $this->awardXp(
            user: $user,
            amount: $badge->xp_reward,
            source: 'badge',
            description: "Earned badge: {$badge->name}",
            badge: $badge,
        );
    }

    /**
     * Award welcome XP + badge to new user.
     */
    public function awardWelcomeBonus(User $user, ?Badge $welcomeBadge = null): void
    {
        // Welcome XP
        $this->awardXp(
            user: $user,
            amount: 10,
            source: 'welcome',
            description: 'Welcome to LakbayXP!',
        );

        // Welcome badge
        if ($welcomeBadge && !$user->badges()->where('badges.id', $welcomeBadge->id)->exists()) {
            $user->badges()->attach($welcomeBadge->id, ['awarded_at' => now()]);

            if ($welcomeBadge->points > 0) {
                $user->increment('total_points', $welcomeBadge->points);
                $user->increment('available_points', $welcomeBadge->points);
            }

            if (($welcomeBadge->xp_reward ?? 0) > 0) {
                $this->awardBadgeXp($user, $welcomeBadge);
            }
        }
    }

    /**
     * Get XP breakdown by category for a user.
     */
    public function getCategoryXp(User $user): array
    {
        $categorized = XpHistory::where('user_id', $user->id)
            ->whereNotNull('category')
            ->selectRaw('category, SUM(amount) as total_xp')
            ->groupBy('category')
            ->pluck('total_xp', 'category')
            ->toArray();

        $others = XpHistory::where('user_id', $user->id)
            ->whereNull('category')
            ->sum('amount');

        if ($others > 0) {
            $categorized['others'] = (int) $others;
        }

        return $categorized;
    }

    /**
     * Get XP history for a user.
     */
    public function getHistory(User $user, int $perPage = 15)
    {
        return XpHistory::where('user_id', $user->id)
            ->with(['place:id,name,slug', 'badge:id,name,slug', 'event:id,title,slug', 'grantedBy:id,name'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    private function autoDescription(string $source, int $amount, ?Place $place, ?Badge $badge, ?Event $event): string
    {
        return match ($source) {
            'place_unlock' => "Unlocked {$place?->name} (+{$amount} XP)",
            'badge' => "Earned badge: {$badge?->name} (+{$amount} XP)",
            'event' => "Completed event: {$event?->title} (+{$amount} XP)",
            'welcome' => "Welcome to LakbayXP! (+{$amount} XP)",
            'admin' => "Admin granted +{$amount} XP",
            'promo' => "Promo bonus +{$amount} XP",
            default => "+{$amount} XP",
        };
    }

    // ── Level calculation (unchanged) ──

    public function calculateLevel(int $totalXp): int
    {
        $level = 1;
        $cumulativeXp = 0;
        for ($i = 2; $i <= self::MAX_LEVEL; $i++) {
            $cumulativeXp += $this->xpRequiredForLevel($i);
            if ($totalXp < $cumulativeXp) return $level;
            $level = $i;
        }
        return self::MAX_LEVEL;
    }

    public function xpRequiredForLevel(int $level): int
    {
        if ($level <= 1) return 0;
        return (int) round(100 * $level * 1.2);
    }

    public function totalXpForLevel(int $level): int
    {
        $total = 0;
        for ($i = 2; $i <= $level; $i++) {
            $total += $this->xpRequiredForLevel($i);
        }
        return $total;
    }

    public function getProgress(User $user): array
    {
        $currentLevelXp = $this->totalXpForLevel($user->level);
        $nextLevelXp = $user->level >= self::MAX_LEVEL
            ? $currentLevelXp
            : $this->totalXpForLevel($user->level + 1);

        $xpInCurrentLevel = $user->xp - $currentLevelXp;
        $xpNeededForNext = $nextLevelXp - $currentLevelXp;
        $progressPercent = $xpNeededForNext > 0
            ? min(100, round(($xpInCurrentLevel / $xpNeededForNext) * 100))
            : 100;

        return [
            'level' => $user->level,
            'total_xp' => $user->xp,
            'xp_in_current_level' => $xpInCurrentLevel,
            'xp_needed_for_next' => $xpNeededForNext,
            'progress_percent' => $progressPercent,
            'is_max_level' => $user->level >= self::MAX_LEVEL,
        ];
    }
}
