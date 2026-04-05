<?php

namespace App\Services;

use App\Enums\PlaceCategory;
use App\Models\Place;
use Illuminate\Validation\ValidationException;

class PlaceService
{
    /**
     * Create a new place.
     *
     * @param array $data
     * @return Place
     * @throws ValidationException
     */
    public function create(array $data): Place
    {
        // Validate PlaceCategory enum
        if (isset($data['category']) && is_string($data['category'])) {
            $category = PlaceCategory::tryFrom($data['category']);
            if ($category === null) {
                throw ValidationException::withMessages([
                    'category' => ['The selected category is invalid.'],
                ]);
            }
            $data['category'] = $category;
        }

        return Place::create($data);
    }

    /**
     * Update an existing place.
     *
     * @param Place $place
     * @param array $data
     * @return Place
     * @throws ValidationException
     */
    public function update(Place $place, array $data): Place
    {
        if (isset($data['category']) && is_string($data['category'])) {
            $category = PlaceCategory::tryFrom($data['category']);
            if ($category === null) {
                throw ValidationException::withMessages([
                    'category' => ['The selected category is invalid.'],
                ]);
            }
            $data['category'] = $category;
        }

        $place->update($data);

        return $place->refresh();
    }

    /**
     * Deactivate a place (soft-disable).
     *
     * @param Place $place
     * @return Place
     */
    public function deactivate(Place $place): Place
    {
        $place->update(['is_active' => false]);

        return $place->refresh();
    }
}
