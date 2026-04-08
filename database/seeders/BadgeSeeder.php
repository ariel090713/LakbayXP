<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Welcome Explorer',
                'slug' => 'welcome-explorer',
                'description' => 'Welcome to LakbayXP! Start your adventure.',
                'criteria_type' => 'unlock_count',
                'criteria_value' => ['count' => 0],
                'rarity' => 'common',
                'points' => 5,
                'xp_reward' => 10,
                'is_repeatable' => false,
            ],
            [
                'name' => 'First Steps',
                'slug' => 'first-steps',
                'description' => 'Unlock your very first place.',
                'criteria_type' => 'unlock_count',
                'criteria_value' => ['count' => 1],
                'rarity' => 'common',
                'points' => 10,
                'xp_reward' => 20,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Explorer',
                'slug' => 'explorer',
                'description' => 'Unlock 5 places across the Philippines.',
                'criteria_type' => 'unlock_count',
                'criteria_value' => ['count' => 5],
                'rarity' => 'common',
                'points' => 25,
                'xp_reward' => 50,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Trailblazer',
                'slug' => 'trailblazer',
                'description' => 'Unlock 15 places. You are unstoppable!',
                'criteria_type' => 'unlock_count',
                'criteria_value' => ['count' => 15],
                'rarity' => 'rare',
                'points' => 50,
                'xp_reward' => 100,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Conqueror',
                'slug' => 'conqueror',
                'description' => 'Unlock 30 places. A true Filipino adventurer.',
                'criteria_type' => 'unlock_count',
                'criteria_value' => ['count' => 30],
                'rarity' => 'epic',
                'points' => 100,
                'xp_reward' => 200,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Mountain Goat',
                'slug' => 'mountain-goat',
                'description' => 'Unlock 3 mountain destinations.',
                'criteria_type' => 'category_count',
                'criteria_value' => ['category' => 'mountain', 'count' => 3],
                'rarity' => 'rare',
                'points' => 30,
                'xp_reward' => 60,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Beach Bum',
                'slug' => 'beach-bum',
                'description' => 'Unlock 3 beach destinations.',
                'criteria_type' => 'category_count',
                'criteria_value' => ['category' => 'beach', 'count' => 3],
                'rarity' => 'rare',
                'points' => 30,
                'xp_reward' => 60,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Island Hopper',
                'slug' => 'island-hopper',
                'description' => 'Unlock 3 island destinations.',
                'criteria_type' => 'category_count',
                'criteria_value' => ['category' => 'island', 'count' => 3],
                'rarity' => 'rare',
                'points' => 30,
                'xp_reward' => 60,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Cordillera Wanderer',
                'slug' => 'cordillera-wanderer',
                'description' => 'Unlock 3 places in the Cordillera region.',
                'criteria_type' => 'region_count',
                'criteria_value' => ['region' => 'Cordillera Administrative Region (CAR)', 'count' => 3],
                'rarity' => 'epic',
                'points' => 50,
                'xp_reward' => 100,
                'is_repeatable' => false,
            ],
            [
                'name' => 'Streak Master',
                'slug' => 'streak-master',
                'description' => 'Maintain a 7-day unlock streak.',
                'criteria_type' => 'streak',
                'criteria_value' => ['days' => 7],
                'rarity' => 'legendary',
                'points' => 100,
                'xp_reward' => 200,
                'is_repeatable' => true,
                'max_claims' => 10,
            ],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(['slug' => $b['slug']], array_merge($b, ['is_active' => true]));
        }

        $this->command->info('✅ BadgeSeeder done — ' . count($badges) . ' badges.');
    }
}
