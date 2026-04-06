<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * Return users ranked by total unlocked places descending.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->where('role', 'user')
            ->where('xp', '>', 0)
            ->withCount(['unlockedPlaces', 'badges'])
            ->orderByDesc('level')
            ->orderByDesc('xp')
            ->paginate($request->input('per_page', 20));

        return response()->json($users);
    }
}
