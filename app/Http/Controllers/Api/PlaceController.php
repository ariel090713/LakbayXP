<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlaceCategory;
use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    /**
     * Return active places with optional filtering by category, region, and search by name.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Place::query()->where('is_active', true);

        // Category filter
        if ($request->filled('category')) {
            $category = PlaceCategory::tryFrom($request->input('category'));
            if ($category) {
                $query->where('category', $category);
            }
        }

        // Region filter (handles both short and full region names)
        if ($request->filled('region')) {
            $region = $request->input('region');
            $query->where(function ($q) use ($region) {
                $q->where('region', $region)
                  ->orWhere('region', 'like', "%{$region}%")
                  ->orWhereRaw('? LIKE CONCAT("%", region, "%")', [$region]);
            });
        }

        // Province filter
        if ($request->filled('province')) {
            $province = $request->input('province');
            $query->where(function ($q) use ($province) {
                $q->where('province', $province)
                  ->orWhere('province', 'like', "%{$province}%");
            });
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Unlock status filter (unlocked / locked for current user)
        if ($request->filled('unlock')) {
            $userId = $request->user()->id;
            $unlockFilter = $request->input('unlock');
            if ($unlockFilter === 'unlocked') {
                $query->whereHas('unlockedByUsers', fn ($q) => $q->where('users.id', $userId));
            } elseif ($unlockFilter === 'locked') {
                $query->whereDoesntHave('unlockedByUsers', fn ($q) => $q->where('users.id', $userId));
            }
        }

        $isNearMe = false;

        // Near me (Haversine)
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');
            $radius = (int) $request->input('radius', 100);
            $isNearMe = true;

            $query->whereNotNull('latitude')->whereNotNull('longitude');
            $query->selectRaw('places.*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance_km', [$lat, $lng, $lat]);
            $query->havingRaw('distance_km <= ?', [$radius]);
            $query->orderBy('distance_km');
        }

        // Sort (secondary to near_me if both present)
        if ($request->filled('sort')) {
            match ($request->input('sort')) {
                'popular' => $query->withCount('unlockedByUsers')->orderByDesc('unlocked_by_users_count'),
                'xp' => $query->orderByDesc('xp_reward'),
                'newest' => $query->orderByDesc('created_at'),
                default => null,
            };
        } elseif (!$isNearMe) {
            $query->orderBy('name');
        }

        $places = $query->paginate($request->input('per_page', 15));

        // Add is_unlocked flag for each place
        $userId = $request->user()->id;
        $unlockedIds = \App\Models\PlaceUnlock::where('user_id', $userId)
            ->whereIn('place_id', $places->pluck('id'))
            ->pluck('place_id')
            ->toArray();

        $places->getCollection()->transform(function ($place) use ($unlockedIds) {
            $place->is_unlocked = in_array($place->id, $unlockedIds);
            return $place;
        });

        return response()->json($places);
    }

    /**
     * Return a single place (only if active).
     */
    public function show(Request $request, Place $place): JsonResponse
    {
        if (!$place->is_active) {
            return response()->json(['message' => 'Place not found.'], 404);
        }

        $place->load(['images', 'meta']);
        $place->loadCount('unlockedByUsers');
        $place->is_unlocked = $place->unlockedByUsers()->where('users.id', $request->user()->id)->exists();

        // Average rating
        $place->average_rating = round(\App\Models\Review::where('reviewable_type', 'place')->where('reviewable_id', $place->id)->avg('rating'), 1);
        $place->reviews_count = \App\Models\Review::where('reviewable_type', 'place')->where('reviewable_id', $place->id)->count();

        return response()->json($place);
    }
}
