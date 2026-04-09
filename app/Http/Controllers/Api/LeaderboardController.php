<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Paginated leaderboard with dense ranking (ties = same rank).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $users = User::query()
            ->where('role', 'user')
            ->where('xp', '>', 0)
            ->withCount(['unlockedPlaces', 'badges', 'followers'])
            ->orderByDesc('level')
            ->orderByDesc('xp')
            ->paginate($perPage);

        // Calculate dense rank for each user on this page
        // Count distinct (level, xp) combos that are strictly higher
        $users->getCollection()->transform(function ($user) {
            $rank = User::where('role', 'user')
                ->where(function ($q) use ($user) {
                    $q->where('level', '>', $user->level)
                      ->orWhere(function ($q2) use ($user) {
                          $q2->where('level', $user->level)
                             ->where('xp', '>', $user->xp);
                      });
                })
                ->selectRaw('DISTINCT level, xp')
                ->count() + 1;

            $user->rank = $rank;
            return $user;
        });

        return response()->json($users);
    }
}
