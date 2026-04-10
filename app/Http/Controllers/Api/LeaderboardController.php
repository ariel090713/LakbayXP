<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Paginated leaderboard. No ties — tiebreaker: older account ranks higher.
     * Sort: level desc → xp desc → created_at asc
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        $users = User::query()
            ->where('xp', '>', 0)
            ->withCount(['unlockedPlaces', 'badges', 'followers'])
            ->orderByDesc('level')
            ->orderByDesc('xp')
            ->orderBy('created_at')
            ->paginate($perPage);

        // Add sequential rank based on page offset
        $users->getCollection()->transform(function ($user, $index) use ($offset) {
            $user->rank = $offset + $index + 1;
            return $user;
        });

        return response()->json($users);
    }
}
