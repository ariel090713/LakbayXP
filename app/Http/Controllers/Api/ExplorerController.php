<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExplorerController extends Controller
{
    /**
     * List explorers with smart sorting, filters, and near-me.
     *
     * Sort options: top (level+xp), active (recent unlocks), popular (most followers), near_me
     * Filters: search, min_level, max_level, has_badge, region
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $sort = $request->input('sort', 'top'); // top, active, popular, near_me, newest
        $perPage = $request->input('per_page', 20);

        $query = User::where('id', '!=', $user->id)
            ->withCount(['unlockedPlaces', 'badges', 'followers', 'following']);

        // ── Search ──
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }

        // ── Level filter ──
        if ($request->filled('min_level')) {
            $query->where('level', '>=', (int) $request->input('min_level'));
        }
        if ($request->filled('max_level')) {
            $query->where('level', '<=', (int) $request->input('max_level'));
        }

        // ── Has badge filter ──
        if ($request->filled('has_badge')) {
            $badgeId = $request->input('has_badge');
            $query->whereHas('badges', function ($q) use ($badgeId) {
                $q->where('badges.id', $badgeId);
            });
        }

        // ── Only with location (for near_me) ──
        if ($sort === 'near_me') {
            $lat = $request->input('lat');
            $lng = $request->input('lng');

            if (!$lat || !$lng) {
                return response()->json(['message' => 'lat and lng required for near_me sort.'], 422);
            }

            $query->whereNotNull('latitude')->whereNotNull('longitude');

            // Add distance calculation without duplicating columns
            $query->selectRaw('(
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance_km', [$lat, $lng, $lat]);

            $radius = $request->input('radius', 100);
            $query->havingRaw('distance_km <= ?', [$radius]);
            $query->orderBy('distance_km');
        }

        // ── Sorting ──
        match ($sort) {
            'top' => $query->orderByDesc('level')->orderByDesc('xp'),
            'active' => $query->orderByDesc(
                DB::raw('(SELECT MAX(created_at) FROM place_unlocks WHERE place_unlocks.user_id = users.id)')
            ),
            'popular' => $query->orderByDesc('followers_count'),
            'newest' => $query->orderByDesc('created_at'),
            'near_me' => null, // already sorted by distance above
            default => $query->orderByDesc('level')->orderByDesc('xp'),
        };

        $explorers = $query->paginate($perPage);

        // Check which ones the current user follows
        $followingIds = $user->following()->pluck('users.id')->toArray();

        $explorers->getCollection()->transform(function ($explorer) use ($followingIds) {
            $explorer->is_following = in_array($explorer->id, $followingIds);
            return $explorer;
        });

        return response()->json($explorers);
    }

    /**
     * Update current user's location.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $request->user()->update($validated);

        return response()->json(['message' => 'Location updated.']);
    }
}
