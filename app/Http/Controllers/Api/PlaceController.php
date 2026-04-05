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

        if ($request->filled('category')) {
            $category = PlaceCategory::tryFrom($request->input('category'));
            if ($category) {
                $query->where('category', $category);
            }
        }

        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $places = $query->orderBy('name')->paginate(15);

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
