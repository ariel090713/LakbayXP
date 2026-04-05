<?php

namespace App\Services;

use App\Enums\ExplorerLevel;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Collection;

class AchievementService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected XpService $xpService,
    ) {}
    /**
     * Pure computation: determine explorer level from unlock count.
     *
     * 0-4 → BeginnerExplorer
     * 5-14 → WeekendWanderer
     * 15-29 → TrailHunter
     * 30+ → SummitCollector
     */
    public function calculateExplorerLevel(User $user): ExplorerLevel
    {
        $count = $user->unlockedPlaces()->count();

        return match (true) {
            $count >= 30 => ExplorerLevel::SummitCollector,
            $count >= 15 => ExplorerLevel::TrailHunter,
            $count >= 5  => ExplorerLevel::WeekendWanderer,
            default      => ExplorerLevel::BeginnerExplorer,
        };
    }

    /**
     * Evaluate all active badges not yet awarded, compare against user's
     * unlock stats, attach newly earned badges, recalculate explorer level.
     */
    public function checkAndAwardBadges(User $user): Collection
    {
        $newBadges = collect();
        $existingBadgeIds = $user->badges()->pluck('badges.id');
        $candidateBadges = Badge::where('is_active', true)
            ->whereNotIn('id', $existingBadgeIds)
            ->get();

        $unlockStats = $this->getUserUnlockStats($user);

        foreach ($candidateBadges as $badge) {
            if ($this->meetsCriteria($badge, $unlockStats)) {
                $user->badges()->attach($badge->id, ['awarded_at' => now()]);
                $newBadges->push($badge);
                $this->notificationService->notifyBadgeAwarded($user, $badge);

                // Award points from badge
                if ($badge->points > 0) {
                    $user->increment('total_points', $badge->points);
                    $user->increment('available_points', $badge->points);
                }

                // Award XP from badge
                if ($badge->xp_reward > 0) {
                    $this->xpService->awardXp($user, $badge->xp_reward);
                }
            }
        }

        // Recalculate explorer level
        $newLevel = $this->calculateExplorerLevel($user);
        if ($user->explorer_level !== $newLevel) {
            $user->update(['explorer_level' => $newLevel]);
        }

        return $newBadges;
    }

    /**
     * Compute total unlocks, per-category counts, per-region counts, current streak.
     */
    public function getUserUnlockStats(User $user): array
    {
        $unlocks = $user->unlockedPlaces()->withPivot('created_at')->get();

        $total = $unlocks->count();

        $byCategory = $unlocks->groupBy(fn ($place) => $place->category->value)
            ->map->count()
            ->toArray();

        $byRegion = $unlocks->groupBy('region')
            ->map->count()
            ->toArray();

        $currentStreak = $this->calculateStreak($user);

        return [
            'total' => $total,
            'by_category' => $byCategory,
            'by_region' => $byRegion,
            'current_streak' => $currentStreak,
        ];
    }

    /**
     * Match badge criteria_type against stats.
     */
    public function meetsCriteria(Badge $badge, array $stats): bool
    {
        return match ($badge->criteria_type) {
            'unlock_count' => $stats['total'] >= ($badge->criteria_value['count'] ?? 0),
            'category_count' => ($stats['by_category'][$badge->criteria_value['category'] ?? ''] ?? 0)
                >= ($badge->criteria_value['count'] ?? 0),
            'region_count' => ($stats['by_region'][$badge->criteria_value['region'] ?? ''] ?? 0)
                >= ($badge->criteria_value['count'] ?? 0),
            'streak' => $stats['current_streak'] >= ($badge->criteria_value['days'] ?? 0),
            default => false,
        };
    }

    /**
     * Calculate the user's current consecutive-day unlock streak.
     */
    private function calculateStreak(User $user): int
    {
        $unlockDates = $user->unlockedPlaces()
            ->withPivot('created_at')
            ->get()
            ->pluck('pivot.created_at')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->toDateString())
            ->unique()
            ->sort()
            ->values()
            ->reverse()
            ->values();

        if ($unlockDates->isEmpty()) {
            return 0;
        }

        $streak = 1;
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Streak must include today or yesterday to be "current"
        if ($unlockDates->first() !== $today && $unlockDates->first() !== $yesterday) {
            return 0;
        }

        for ($i = 0; $i < $unlockDates->count() - 1; $i++) {
            $current = \Carbon\Carbon::parse($unlockDates[$i]);
            $next = \Carbon\Carbon::parse($unlockDates[$i + 1]);

            if ($current->diffInDays($next) === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }
}
