<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\ExplorerLevel;
use App\Enums\PlaceCategory;
use App\Enums\UnlockMethod;
use App\Enums\UserRole;
use App\Models\Badge;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Place;
use App\Models\PlaceUnlock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        // 2. Organizer users
        $organizer1 = User::factory()->create([
            'name' => 'Organizer One',
            'email' => 'organizer1@example.com',
            'username' => 'organizer1',
            'password' => Hash::make('password'),
            'role' => UserRole::Organizer,
            'is_verified_organizer' => true,
        ]);

        $organizer2 = User::factory()->create([
            'name' => 'Organizer Two',
            'email' => 'organizer2@example.com',
            'username' => 'organizer2',
            'password' => Hash::make('password'),
            'role' => UserRole::Organizer,
            'is_verified_organizer' => false,
        ]);

        // 3. Regular users
        $users = User::factory(5)->create([
            'role' => UserRole::User,
        ]);

        // 4. Places (10 — various categories)
        $placeData = [
            ['name' => 'Mt. Pulag', 'slug' => 'mt-pulag', 'category' => PlaceCategory::Mountain, 'region' => 'Cordillera', 'province' => 'Benguet'],
            ['name' => 'Boracay White Beach', 'slug' => 'boracay-white-beach', 'category' => PlaceCategory::Beach, 'region' => 'Western Visayas', 'province' => 'Aklan'],
            ['name' => 'Siargao Island', 'slug' => 'siargao-island', 'category' => PlaceCategory::Island, 'region' => 'Caraga', 'province' => 'Surigao del Norte'],
            ['name' => 'Kawasan Falls', 'slug' => 'kawasan-falls', 'category' => PlaceCategory::Falls, 'region' => 'Central Visayas', 'province' => 'Cebu'],
            ['name' => 'Chico River', 'slug' => 'chico-river', 'category' => PlaceCategory::River, 'region' => 'Cordillera', 'province' => 'Kalinga'],
            ['name' => 'Lake Holon', 'slug' => 'lake-holon', 'category' => PlaceCategory::Lake, 'region' => 'SOCCSKSARGEN', 'province' => 'South Cotabato'],
            ['name' => 'Anawangin Cove', 'slug' => 'anawangin-cove', 'category' => PlaceCategory::Campsite, 'region' => 'Central Luzon', 'province' => 'Zambales'],
            ['name' => 'Intramuros', 'slug' => 'intramuros', 'category' => PlaceCategory::Historical, 'region' => 'NCR', 'province' => 'Manila'],
            ['name' => 'Binondo Food Walk', 'slug' => 'binondo-food-walk', 'category' => PlaceCategory::FoodDestination, 'region' => 'NCR', 'province' => 'Manila'],
            ['name' => 'Halsema Highway', 'slug' => 'halsema-highway', 'category' => PlaceCategory::RoadTrip, 'region' => 'Cordillera', 'province' => 'Benguet'],
        ];

        $places = collect();
        foreach ($placeData as $pd) {
            $places->push(Place::create(array_merge($pd, [
                'description' => "A beautiful {$pd['category']->value} destination in {$pd['province']}.",
                'latitude' => fake()->latitude(6, 18),
                'longitude' => fake()->longitude(118, 127),
                'is_active' => true,
                'created_by' => $admin->id,
            ])));
        }

        // 5. Events (5 — various statuses)
        $events = collect();

        $events->push(Event::create([
            'organizer_id' => $organizer1->id,
            'place_id' => $places[0]->id,
            'title' => 'Mt. Pulag Sea of Clouds',
            'slug' => 'mt-pulag-sea-of-clouds',
            'description' => 'Witness the famous sea of clouds at the summit.',
            'category' => PlaceCategory::Mountain,
            'event_date' => now()->addWeeks(2),
            'meeting_place' => 'Baguio City, Session Road',
            'fee' => 3500.00,
            'max_slots' => 20,
            'requirements' => ['valid_id', 'medical_certificate'],
            'status' => EventStatus::Published,
            'auto_approve_bookings' => false,
        ]));

        $events->push(Event::create([
            'organizer_id' => $organizer1->id,
            'place_id' => $places[1]->id,
            'title' => 'Boracay Beach Cleanup',
            'slug' => 'boracay-beach-cleanup',
            'description' => 'Join us for a beach cleanup and island tour.',
            'category' => PlaceCategory::Beach,
            'event_date' => now()->addMonth(),
            'meeting_place' => 'Caticlan Jetty Port',
            'fee' => 2000.00,
            'max_slots' => 30,
            'requirements' => ['valid_id'],
            'status' => EventStatus::Published,
            'auto_approve_bookings' => true,
        ]));

        $events->push(Event::create([
            'organizer_id' => $organizer2->id,
            'place_id' => $places[2]->id,
            'title' => 'Siargao Surf Camp',
            'slug' => 'siargao-surf-camp',
            'description' => 'Learn to surf at Cloud 9.',
            'category' => PlaceCategory::Island,
            'event_date' => now()->addWeeks(3),
            'meeting_place' => 'Siargao Airport',
            'fee' => 5000.00,
            'max_slots' => 15,
            'requirements' => ['valid_id', 'waiver'],
            'status' => EventStatus::Draft,
            'auto_approve_bookings' => false,
        ]));

        $events->push(Event::create([
            'organizer_id' => $organizer2->id,
            'place_id' => $places[3]->id,
            'title' => 'Kawasan Canyoneering',
            'slug' => 'kawasan-canyoneering',
            'description' => 'Canyoneering adventure ending at Kawasan Falls.',
            'category' => PlaceCategory::Falls,
            'event_date' => now()->subWeek(),
            'meeting_place' => 'Cebu City South Bus Terminal',
            'fee' => 2500.00,
            'max_slots' => 25,
            'requirements' => ['valid_id'],
            'status' => EventStatus::Completed,
            'auto_approve_bookings' => true,
        ]));

        $events->push(Event::create([
            'organizer_id' => $organizer1->id,
            'place_id' => $places[4]->id,
            'title' => 'Chico River Rafting',
            'slug' => 'chico-river-rafting',
            'description' => 'White water rafting on the Chico River.',
            'category' => PlaceCategory::River,
            'event_date' => now()->subMonth(),
            'meeting_place' => 'Tabuk City',
            'fee' => 1800.00,
            'max_slots' => 10,
            'requirements' => ['valid_id', 'waiver'],
            'status' => EventStatus::Cancelled,
            'auto_approve_bookings' => false,
        ]));

        // 6. Bookings
        // Book users into the published events
        foreach ($users->take(3) as $user) {
            Booking::create([
                'event_id' => $events[0]->id,
                'user_id' => $user->id,
                'status' => BookingStatus::Approved,
                'approved_at' => now(),
            ]);
        }

        foreach ($users->take(2) as $user) {
            Booking::create([
                'event_id' => $events[1]->id,
                'user_id' => $user->id,
                'status' => BookingStatus::Approved,
                'approved_at' => now(),
            ]);
        }

        // Completed event bookings
        foreach ($users->take(4) as $user) {
            Booking::create([
                'event_id' => $events[3]->id,
                'user_id' => $user->id,
                'status' => BookingStatus::Approved,
                'approved_at' => now()->subWeeks(2),
            ]);
        }

        // A pending booking
        Booking::create([
            'event_id' => $events[0]->id,
            'user_id' => $users[3]->id,
            'status' => BookingStatus::Pending,
        ]);

        // 7. Badges (5 — various criteria types)
        $badges = collect();

        $badges->push(Badge::create([
            'name' => 'First Steps',
            'slug' => 'first-steps',
            'description' => 'Unlock your first place.',
            'criteria_type' => 'unlock_count',
            'criteria_value' => ['count' => 1],
            'is_active' => true,
        ]));

        $badges->push(Badge::create([
            'name' => 'Explorer',
            'slug' => 'explorer',
            'description' => 'Unlock 5 places.',
            'criteria_type' => 'unlock_count',
            'criteria_value' => ['count' => 5],
            'is_active' => true,
        ]));

        $badges->push(Badge::create([
            'name' => 'Mountain Goat',
            'slug' => 'mountain-goat',
            'description' => 'Unlock 3 mountain destinations.',
            'criteria_type' => 'category_count',
            'criteria_value' => ['category' => 'mountain', 'count' => 3],
            'is_active' => true,
        ]));

        $badges->push(Badge::create([
            'name' => 'Cordillera Wanderer',
            'slug' => 'cordillera-wanderer',
            'description' => 'Unlock 3 places in the Cordillera region.',
            'criteria_type' => 'region_count',
            'criteria_value' => ['region' => 'Cordillera', 'count' => 3],
            'is_active' => true,
        ]));

        $badges->push(Badge::create([
            'name' => 'Streak Master',
            'slug' => 'streak-master',
            'description' => 'Maintain a 7-day unlock streak.',
            'criteria_type' => 'streak',
            'criteria_value' => ['days' => 7],
            'is_active' => true,
        ]));

        // 8. Place unlocks (for the completed event attendees + some manual)
        // Users who attended the completed Kawasan event unlock that place
        foreach ($users->take(4) as $user) {
            PlaceUnlock::create([
                'user_id' => $user->id,
                'place_id' => $places[3]->id,
                'unlock_method' => UnlockMethod::EventCompletion,
                'event_id' => $events[3]->id,
            ]);
        }

        // Some additional manual unlocks
        PlaceUnlock::create([
            'user_id' => $users[0]->id,
            'place_id' => $places[0]->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);

        PlaceUnlock::create([
            'user_id' => $users[0]->id,
            'place_id' => $places[1]->id,
            'unlock_method' => UnlockMethod::PhotoProof,
            'proof_photo_path' => 'proof-photos/sample.jpg',
        ]);

        PlaceUnlock::create([
            'user_id' => $users[1]->id,
            'place_id' => $places[0]->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);

        // Award "First Steps" badge to users with unlocks
        foreach ($users->take(4) as $user) {
            $user->badges()->attach($badges[0]->id, ['awarded_at' => now()]);
        }
    }
}
