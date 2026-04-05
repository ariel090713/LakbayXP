<?php

namespace App\Services;

use App\Enums\RedemptionStatus;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RewardService
{
    /**
     * Redeem a reward using points.
     */
    public function redeem(User $user, Reward $reward): RewardRedemption
    {
        if (!$reward->is_active) {
            throw new \InvalidArgumentException('This reward is no longer available.');
        }

        if ($reward->availableStock() <= 0) {
            throw new \InvalidArgumentException('This reward is out of stock.');
        }

        if ($user->available_points < $reward->points_cost) {
            throw new \InvalidArgumentException('Not enough points. You need ' . $reward->points_cost . ' points but have ' . $user->available_points . '.');
        }

        return DB::transaction(function () use ($user, $reward) {
            $user->decrement('available_points', $reward->points_cost);

            return RewardRedemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_cost,
                'status' => RedemptionStatus::Pending,
            ]);
        });
    }

    /**
     * Admin approves a redemption.
     */
    public function approve(RewardRedemption $redemption): RewardRedemption
    {
        if ($redemption->status !== RedemptionStatus::Pending) {
            throw new \InvalidArgumentException('Only pending redemptions can be approved.');
        }

        $redemption->update([
            'status' => RedemptionStatus::Approved,
            'approved_at' => now(),
        ]);

        return $redemption->refresh();
    }

    /**
     * Admin rejects a redemption — refund points.
     */
    public function reject(RewardRedemption $redemption, ?string $notes = null): RewardRedemption
    {
        if ($redemption->status !== RedemptionStatus::Pending) {
            throw new \InvalidArgumentException('Only pending redemptions can be rejected.');
        }

        return DB::transaction(function () use ($redemption, $notes) {
            $redemption->user->increment('available_points', $redemption->points_spent);

            $redemption->update([
                'status' => RedemptionStatus::Rejected,
                'admin_notes' => $notes,
            ]);

            return $redemption->refresh();
        });
    }

    /**
     * Mark redemption as claimed (user picked up the freebie).
     */
    public function markClaimed(RewardRedemption $redemption): RewardRedemption
    {
        if ($redemption->status !== RedemptionStatus::Approved) {
            throw new \InvalidArgumentException('Only approved redemptions can be marked as claimed.');
        }

        $redemption->update([
            'status' => RedemptionStatus::Claimed,
            'claimed_at' => now(),
        ]);

        return $redemption->refresh();
    }

    /**
     * Recalculate a user's points from their earned badges.
     */
    public function recalculatePoints(User $user): void
    {
        $totalPoints = $user->badges()->sum('points');
        $spentPoints = $user->redemptions()
            ->whereIn('status', [RedemptionStatus::Pending, RedemptionStatus::Approved, RedemptionStatus::Claimed])
            ->sum('points_spent');

        $user->update([
            'total_points' => $totalPoints,
            'available_points' => max(0, $totalPoints - $spentPoints),
        ]);
    }
}
