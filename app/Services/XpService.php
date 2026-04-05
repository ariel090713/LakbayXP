<?php

namespace App\Services;

use App\Models\User;

class XpService
{
    const MAX_LEVEL = 100;

    /**
     * Award XP to a user and recalculate their level.
     */
    public function awardXp(User $user, int $amount): array
    {
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
     * Calculate level from total XP.
     * Uses cumulative XP thresholds: each level N requires 100 * N * 1.2 XP.
     */
    public function calculateLevel(int $totalXp): int
    {
        $level = 1;
        $cumulativeXp = 0;

        for ($i = 2; $i <= self::MAX_LEVEL; $i++) {
            $cumulativeXp += $this->xpRequiredForLevel($i);
            if ($totalXp < $cumulativeXp) {
                return $level;
            }
            $level = $i;
        }

        return self::MAX_LEVEL;
    }

    /**
     * XP required to go from level (N-1) to level N.
     * Formula: 100 * N * 1.2 (rounded)
     */
    public function xpRequiredForLevel(int $level): int
    {
        if ($level <= 1) return 0;
        return (int) round(100 * $level * 1.2);
    }

    /**
     * Total cumulative XP needed to reach a given level.
     */
    public function totalXpForLevel(int $level): int
    {
        $total = 0;
        for ($i = 2; $i <= $level; $i++) {
            $total += $this->xpRequiredForLevel($i);
        }
        return $total;
    }

    /**
     * Get XP progress info for a user.
     */
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
