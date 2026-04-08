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

        // Region filter
        if ($request->filled('region')) {
            $query->where('region', 'like', '%' . $request->input('region') . '%');
        }

        // Province filter
        if ($request->filled('province')) {
            $query->where('province', 'like', '%' . $request->input('province') . '%');
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Near me (Haversine)
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');
            $radius = (int) $request->input('radius', 100); // default 100km

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
        } else {
            $query->orderBy('name');
        }

        // Sort
        if ($request->filled('sort')) {
            match ($request->input('sort')) {
                'popular' => $query->withCount('unlockedByUsers')->orderByDesc('unlocked_by_users_count'),
                'xp' => $query->orderByDesc('xp_reward'),
                'newest' => $query->orderByDesc('created_at'),
                default => null,
            };
        }

        $places = $query->paginate($request->input('per_page', 15));

        return response()->json($places);
    }

    /**
     * Return a single place (only if active).
     */
    public function show(Place $place): JsonResponse
    {
        if (!$place->is_active) {
            return response()->json(['message' => 'Place not found.'], 404);
        }

        return response()->json($place);
    }
}
