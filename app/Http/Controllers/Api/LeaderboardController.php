<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    /**
     * Return users ranked by total unlocked places descending.
     */
    public function index(): JsonResponse
    {
        $users = User::query()
            ->withCount(['unlockedPlaces', 'badges'])
            ->orderByDesc('unlocked_places_count')
            ->limit(100)
            ->get()
            ->map(fn (User $user) => [
                'username' => $user->username,
                'explorer_level' => $user->explorer_level,
                'unlocked_places_count' => $user->unlocked_places_count,
                'badge_count' => $user->badges_count,
            ]);

        return response()->json(['data' => $users]);
    }
}
