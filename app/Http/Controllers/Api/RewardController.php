<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Services\RewardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function __construct(
        protected RewardService $rewardService,
    ) {}

    /**
     * List available rewards.
     */
    public function index(Request $request): JsonResponse
    {
        $rewards = Reward::where('is_active', true)
            ->orderBy('points_cost')
            ->paginate($request->input('per_page', 15));

        return response()->json($rewards);
    }

    /**
     * Redeem a reward.
     */
    public function redeem(Request $request, Reward $reward): JsonResponse
    {
        try {
            $redemption = $this->rewardService->redeem($request->user(), $reward);

            return response()->json([
                'message' => 'Reward redeemed successfully. Pending admin approval.',
                'redemption' => $redemption->load('reward'),
                'available_points' => $request->user()->fresh()->available_points,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List user's redemption history.
     */
    public function myRedemptions(Request $request): JsonResponse
    {
        $redemptions = $request->user()
            ->redemptions()
            ->with('reward')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json($redemptions);
    }
}
