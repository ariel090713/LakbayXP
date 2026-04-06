<?php

/**
 * Category-specific fields for places.
 * Each category has predefined fields that show in the admin form.
 * Additional custom meta can be added via the place_meta table.
 *
 * Field types: text, number, select, textarea
 */

return [
    'mountain' => [
        ['key' => 'elevation_masl', 'label' => 'Elevation (MASL)', 'type' => 'number', 'placeholder' => 'e.g. 2922'],
        ['key' => 'trail_class', 'label' => 'Trail Class (1-9)', 'type' => 'select', 'options' => [
            '1' => 'Class 1 — Easy trail',
            '2' => 'Class 2 — Minor climb',
            '3' => 'Class 3 — Average climb',
            '4' => 'Class 4 — Difficult trail',
            '5' => 'Class 5 — Major climb',
            '6' => 'Class 6 — Very difficult',
            '7' => 'Class 7 — Extremely difficult',
            '8' => 'Class 8 — Mountaineering',
            '9' => 'Class 9 — Technical climb',
        ]],
        ['key' => 'difficulty_label', 'label' => 'Difficulty', 'type' => 'select', 'options' => [
            'easy' => 'Easy (beginner-friendly)',
            'moderate' => 'Moderate',
            'hard' => 'Hard',
            'extreme' => 'Extreme',
        ]],
        ['key' => 'trail_type', 'label' => 'Trail Type', 'type' => 'select', 'options' => [
            'day_hike' => 'Day Hike',
            'overnight' => 'Overnight',
            'multi_day' => 'Multi-Day',
            'traverse' => 'Traverse',
        ]],
        ['key' => 'estimated_hours', 'label' => 'Estimated Hours (to summit)', 'type' => 'number', 'placeholder' => 'e.g. 6'],
        ['key' => 'jump_off_point', 'label' => 'Jump-off Point', 'type' => 'text', 'placeholder' => 'e.g. Ranger Station, Brgy. Ambangeg'],
        ['key' => 'permit_required', 'label' => 'Permit Required', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
        ['key' => 'guide_required', 'label' => 'Guide Required', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
    ],

    'beach' => [
        ['key' => 'sand_type', 'label' => 'Sand Type', 'type' => 'select', 'options' => [
            'white' => 'White Sand',
            'cream' => 'Cream Sand',
            'golden' => 'Golden Sand',
            'black' => 'Black Sand',
            'pink' => 'Pink Sand',
            'pebble' => 'Pebble',
            'rocky' => 'Rocky',
        ]],
        ['key' => 'water_activity', 'label' => 'Water Activities', 'type' => 'text', 'placeholder' => 'e.g. Snorkeling, Diving, Surfing'],
        ['key' => 'entrance_fee', 'label' => 'Entrance Fee (₱)', 'type' => 'number', 'placeholder' => 'e.g. 100'],
        ['key' => 'best_season', 'label' => 'Best Season', 'type' => 'text', 'placeholder' => 'e.g. March to May'],
        ['key' => 'accessibility', 'label' => 'Accessibility', 'type' => 'select', 'options' => [
            'easy' => 'Easy (road accessible)',
            'moderate' => 'Moderate (short boat ride)',
            'hard' => 'Hard (long travel)',
        ]],
    ],

    'island' => [
        ['key' => 'how_to_get_there', 'label' => 'How to Get There', 'type' => 'textarea', 'placeholder' => 'Boat from...'],
        ['key' => 'boat_ride_minutes', 'label' => 'Boat Ride (minutes)', 'type' => 'number', 'placeholder' => 'e.g. 30'],
        ['key' => 'overnight_allowed', 'label' => 'Overnight Allowed', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
        ['key' => 'entrance_fee', 'label' => 'Entrance Fee (₱)', 'type' => 'number', 'placeholder' => 'e.g. 200'],
        ['key' => 'best_season', 'label' => 'Best Season', 'type' => 'text', 'placeholder' => 'e.g. November to May'],
    ],

    'falls' => [
        ['key' => 'height_meters', 'label' => 'Height (meters)', 'type' => 'number', 'placeholder' => 'e.g. 30'],
        ['key' => 'layers', 'label' => 'Number of Layers/Tiers', 'type' => 'number', 'placeholder' => 'e.g. 3'],
        ['key' => 'swimming_allowed', 'label' => 'Swimming Allowed', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
        ['key' => 'trek_minutes', 'label' => 'Trek to Falls (minutes)', 'type' => 'number', 'placeholder' => 'e.g. 20'],
        ['key' => 'entrance_fee', 'label' => 'Entrance Fee (₱)', 'type' => 'number', 'placeholder' => 'e.g. 50'],
        ['key' => 'difficulty_label', 'label' => 'Trail Difficulty', 'type' => 'select', 'options' => [
            'easy' => 'Easy', 'moderate' => 'Moderate', 'hard' => 'Hard',
        ]],
    ],

    'river' => [
        ['key' => 'activity_type', 'label' => 'Activity Type', 'type' => 'text', 'placeholder' => 'e.g. Rafting, Kayaking, Tubing'],
        ['key' => 'rapids_class', 'label' => 'Rapids Class', 'type' => 'select', 'options' => [
            'I' => 'Class I — Easy',
            'II' => 'Class II — Novice',
            'III' => 'Class III — Intermediate',
            'IV' => 'Class IV — Advanced',
            'V' => 'Class V — Expert',
        ]],
        ['key' => 'length_km', 'label' => 'River Length (km)', 'type' => 'number', 'placeholder' => 'e.g. 15'],
        ['key' => 'best_season', 'label' => 'Best Season', 'type' => 'text', 'placeholder' => 'e.g. June to October'],
    ],

    'lake' => [
        ['key' => 'elevation_masl', 'label' => 'Elevation (MASL)', 'type' => 'number', 'placeholder' => 'e.g. 700'],
        ['key' => 'swimming_allowed', 'label' => 'Swimming Allowed', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
        ['key' => 'boat_allowed', 'label' => 'Boating Allowed', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
        ['key' => 'trek_required', 'label' => 'Trek Required', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
        ['key' => 'entrance_fee', 'label' => 'Entrance Fee (₱)', 'type' => 'number', 'placeholder' => 'e.g. 50'],
    ],

    'campsite' => [
        ['key' => 'campsite_type', 'label' => 'Campsite Type', 'type' => 'select', 'options' => [
            'beach' => 'Beach Camping',
            'mountain' => 'Mountain Camping',
            'forest' => 'Forest Camping',
            'lakeside' => 'Lakeside Camping',
            'riverside' => 'Riverside Camping',
        ]],
        ['key' => 'tent_rental', 'label' => 'Tent Rental Available', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
        ['key' => 'facilities', 'label' => 'Facilities', 'type' => 'text', 'placeholder' => 'e.g. Restroom, Water Source, Fire Pit'],
        ['key' => 'camping_fee', 'label' => 'Camping Fee (₱)', 'type' => 'number', 'placeholder' => 'e.g. 150'],
        ['key' => 'signal_available', 'label' => 'Phone Signal', 'type' => 'select', 'options' => ['yes' => 'Yes', 'weak' => 'Weak', 'no' => 'No']],
    ],

    'historical' => [
        ['key' => 'historical_period', 'label' => 'Historical Period', 'type' => 'text', 'placeholder' => 'e.g. Spanish Colonial Era'],
        ['key' => 'year_built', 'label' => 'Year Built/Established', 'type' => 'text', 'placeholder' => 'e.g. 1571'],
        ['key' => 'heritage_status', 'label' => 'Heritage Status', 'type' => 'select', 'options' => [
            'none' => 'None',
            'local' => 'Local Heritage',
            'national' => 'National Heritage',
            'unesco' => 'UNESCO World Heritage',
        ]],
        ['key' => 'entrance_fee', 'label' => 'Entrance Fee (₱)', 'type' => 'number', 'placeholder' => 'e.g. 75'],
        ['key' => 'guided_tour', 'label' => 'Guided Tour Available', 'type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No']],
    ],

    'food_destination' => [
        ['key' => 'cuisine_type', 'label' => 'Cuisine Type', 'type' => 'text', 'placeholder' => 'e.g. Filipino, Chinese-Filipino, Seafood'],
        ['key' => 'must_try', 'label' => 'Must-Try Dish', 'type' => 'text', 'placeholder' => 'e.g. Lechon, Sinigang'],
        ['key' => 'price_range', 'label' => 'Price Range', 'type' => 'select', 'options' => [
            'budget' => 'Budget (₱50-200)',
            'mid' => 'Mid-range (₱200-500)',
            'premium' => 'Premium (₱500+)',
        ]],
        ['key' => 'food_type', 'label' => 'Food Type', 'type' => 'select', 'options' => [
            'restaurant' => 'Restaurant',
            'street_food' => 'Street Food',
            'market' => 'Market/Palengke',
            'food_park' => 'Food Park',
            'cafe' => 'Cafe',
        ]],
    ],

    'road_trip' => [
        ['key' => 'distance_km', 'label' => 'Total Distance (km)', 'type' => 'number', 'placeholder' => 'e.g. 250'],
        ['key' => 'drive_hours', 'label' => 'Drive Time (hours)', 'type' => 'number', 'placeholder' => 'e.g. 5'],
        ['key' => 'route_highlights', 'label' => 'Route Highlights', 'type' => 'textarea', 'placeholder' => 'e.g. Mountain views, rice terraces, zigzag road'],
        ['key' => 'road_condition', 'label' => 'Road Condition', 'type' => 'select', 'options' => [
            'paved' => 'Fully Paved',
            'mixed' => 'Mixed (paved + rough)',
            'rough' => 'Rough/Off-road',
        ]],
        ['key' => 'vehicle_type', 'label' => 'Recommended Vehicle', 'type' => 'select', 'options' => [
            'any' => 'Any vehicle',
            'suv' => 'SUV/4x4 recommended',
            'motorcycle' => 'Motorcycle friendly',
        ]],
    ],

    'hidden_gem' => [
        ['key' => 'discovery_tip', 'label' => 'How to Find It', 'type' => 'textarea', 'placeholder' => 'Directions or tips to find this hidden spot'],
        ['key' => 'crowd_level', 'label' => 'Crowd Level', 'type' => 'select', 'options' => [
            'empty' => 'Almost Empty',
            'few' => 'Few People',
            'moderate' => 'Moderate',
            'crowded' => 'Can Get Crowded',
        ]],
        ['key' => 'best_time', 'label' => 'Best Time to Visit', 'type' => 'text', 'placeholder' => 'e.g. Early morning, weekdays'],
        ['key' => 'entrance_fee', 'label' => 'Entrance Fee (₱)', 'type' => 'number', 'placeholder' => 'e.g. Free or 50'],
    ],
];
