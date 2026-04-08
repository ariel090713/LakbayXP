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
        protected PointsService $pointsService,
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
            ->where(function ($q) use ($existingBadgeIds) {
                $q->whereNotIn('id', $existingBadgeIds)
                  ->orWhere('is_repeatable', true);
            })
            ->get();

        $unlockStats = $this->getUserUnlockStats($user);

        foreach ($candidateBadges as $badge) {
            if ($this->meetsCriteria($badge, $unlockStats)) {
                $existing = $user->badges()->where('badges.id', $badge->id)->first();

                if ($existing) {
                    // Repeatable badge — check max claims
                    if (!$badge->is_repeatable) continue;
                    $currentClaims = $existing->pivot->claim_count ?? 1;
                    if ($badge->max_claims && $currentClaims >= $badge->max_claims) continue;

                    $user->badges()->updateExistingPivot($badge->id, [
                        'claim_count' => $currentClaims + 1,
                        'awarded_at' => now(),
                        'is_viewed' => false,
                    ]);
                } else {
                    // First time earning
                    $user->badges()->attach($badge->id, ['awarded_at' => now()]);
                }

                $newBadges->push($badge);
                $this->notificationService->notifyBadgeAwarded($user, $badge);

                // Award points from badge (via PointsService with history)
                $this->pointsService->awardBadgePoints($user, $badge);

                // Award XP from badge (with history)
                if (($badge->xp_reward ?? 0) > 0) {
                    $this->xpService->awardBadgeXp($user, $badge);
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
