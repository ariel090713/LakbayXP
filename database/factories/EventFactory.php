<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Enums\PlaceCategory;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'organizer_id' => User::factory()->state(['role' => UserRole::Organizer]),
            'place_id' => Place::factory(),
            'title' => fake()->unique()->sentence(4),
            'slug' => fake()->unique()->slug(4),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(PlaceCategory::cases()),
            'event_date' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'meeting_place' => fake()->address(),
            'fee' => fake()->randomFloat(2, 0, 5000),
            'max_slots' => fake()->numberBetween(5, 50),
            'requirements' => ['valid_id'],
            'status' => EventStatus::Draft,
            'auto_approve_bookings' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Published]);
    }

    public function full(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Full]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => EventStatus::Completed,
            'event_date' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Cancelled]);
    }
}
