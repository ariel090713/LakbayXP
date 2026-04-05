<?php

namespace Database\Factories;

use App\Enums\PlaceCategory;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Place>
 */
class PlaceFactory extends Factory
{
    protected $model = Place::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'slug' => fake()->unique()->slug(3),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(PlaceCategory::cases()),
            'region' => fake()->state(),
            'province' => fake()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'cover_image_path' => null,
            'category_fields' => null,
            'is_active' => true,
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
