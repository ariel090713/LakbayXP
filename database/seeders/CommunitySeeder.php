<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\Event;
use App\Models\Place;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run PlaceEventSeeder first.');
            return;
        }

        $places = Place::where('is_active', true)->get();
        $events = Event::all();
        $badges = Badge::where('is_active', true)->get();

        $posts = collect();

        // ── Text posts (adventure stories) ──
        $stories = [
            'Just got back from an amazing weekend hike! The trail was challenging but the view at the summit made it all worth it 🏔️✨',
            'Nothing beats waking up to a sea of clouds. This is why I climb. 🌅☁️',
            'First time canyoneering and I\'m hooked! The waterfalls were insane 💧🤙',
            'Road trip to the north was epic. Halsema Highway views are unmatched 🚗⛰️',
            'Beach camping under the stars. No wifi, no problems. Just pure vibes 🏖️🌙',
            'Finally visited the rice terraces. 2000 years of engineering and still standing strong 🏛️🌾',
            'Island hopping day! Crystal clear waters everywhere you look 🏝️💎',
            'Food trip sa Binondo! Siomai, lumpia, hopia — busog na busog 🍜😋',
            'Spelunking adventure in Sagada. Sumaguing Cave is no joke! 🦇🔦',
            'Lake Holon trek was one of the best decisions I\'ve made this year. The crater lake is magical ✨🏞️',
            'Surfing lessons in Siargao! Wiped out 20 times but stood up once — worth it 🏄‍♂️😂',
            'Sunset at Nacpan Beach. El Nido never disappoints 🌅🏖️',
            'Completed my first major climb! Mt. Apo conquered! 🏔️🏆',
            'Camping at Anawangin Cove. The pine trees + beach combo is surreal ⛺🌲',
            'Dove with sea turtles at Apo Island today. Bucket list checked! 🐢🤿',
            'Enchanted River lives up to its name. The blue is unreal 💙🌊',
            'Batanes feels like a different country. Rolling hills everywhere 🏝️💚',
            'Night market food crawl in Baguio! Strawberry taho is life 🍓🥤',
            'Coron wreck diving was an experience I\'ll never forget. History underwater 🚢🤿',
            'Trekked to Tinuy-an Falls — the mini Niagara of the Philippines! 💧🇵🇭',
            'Masungi Georeserve rope courses had my heart racing! Conservation + adventure 🌿🧗',
            'Joined a beach cleanup in Boracay. Let\'s keep our beaches clean! 🏖️♻️',
            'Mt. Pulag at 3AM. Freezing cold but the sunrise was everything 🌄❄️',
            'Kayangan Lake — cleanest lake in the Philippines. The hype is real 💎🏞️',
            'Chocolate Hills in summer mode — they really do turn brown! 🍫🏔️',
            'White water rafting on Chico River! Class 3 rapids are no joke 🌊😱',
            'Palaui Island feels like the end of the world. So remote, so beautiful 🏝️🌏',
            'Siquijor island vibes. Mystical and magical ✨🏝️',
            'Camiguin — the island born of fire. Volcanoes everywhere! 🌋🏝️',
            'Just earned my Explorer badge! 5 places unlocked 🏅🎉',
        ];

        // ── Place unlock posts ──
        $unlockMessages = [
            'Just unlocked {place}! Another pin on my adventure map 📍🗺️',
            '{place} conquered! +{xp} XP earned ⚡🏆',
            'New place unlocked: {place}! The journey continues 🔓✨',
            '{place} ✅ One step closer to Summit Legend! 🏔️👑',
            'Added {place} to my collection! {xp} XP closer to leveling up ⚡',
        ];

        // ── Badge earned posts ──
        $badgeMessages = [
            'Just earned the {badge} badge! 🏅🎉',
            'New achievement unlocked: {badge}! +{points} points 🏆⭐',
            '{badge} badge earned! Collecting them all 🏅💪',
        ];

        $this->command->info('Creating posts...');

        // Create text posts
        foreach ($stories as $i => $story) {
            $user = $users->random();
            $place = rand(0, 1) ? $places->random() : null;
            $event = rand(0, 1) && $events->isNotEmpty() ? $events->random() : null;

            $posts->push(Post::create([
                'user_id' => $user->id,
                'content' => $story,
                'type' => 'text',
                'place_id' => $place?->id,
                'event_id' => $event?->id,
                'created_at' => now()->subHours(rand(1, 168)), // last 7 days
                'updated_at' => now()->subHours(rand(1, 168)),
            ]));
        }

        // Create place unlock posts
        $unlocks = \App\Models\PlaceUnlock::with('place')->take(20)->get();
        foreach ($unlocks as $unlock) {
            if (!$unlock->place) continue;
            $template = $unlockMessages[array_rand($unlockMessages)];
            $content = str_replace(
                ['{place}', '{xp}'],
                [$unlock->place->name, $unlock->place->xp_reward ?? 0],
                $template
            );

            $posts->push(Post::create([
                'user_id' => $unlock->user_id,
                'content' => $content,
                'type' => 'place_unlock',
                'place_id' => $unlock->place_id,
                'created_at' => $unlock->created_at ?? now()->subDays(rand(1, 14)),
                'updated_at' => $unlock->created_at ?? now()->subDays(rand(1, 14)),
            ]));
        }

        // Create badge earned posts
        $userBadges = \DB::table('user_badges')->take(10)->get();
        foreach ($userBadges as $ub) {
            $badge = $badges->firstWhere('id', $ub->badge_id);
            if (!$badge) continue;
            $template = $badgeMessages[array_rand($badgeMessages)];
            $content = str_replace(
                ['{badge}', '{points}'],
                [$badge->name, $badge->points ?? 0],
                $template
            );

            $posts->push(Post::create([
                'user_id' => $ub->user_id,
                'content' => $content,
                'type' => 'badge_earned',
                'badge_id' => $badge->id,
                'created_at' => $ub->awarded_at ?? now()->subDays(rand(1, 14)),
                'updated_at' => $ub->awarded_at ?? now()->subDays(rand(1, 14)),
            ]));
        }

        $this->command->info("Created {$posts->count()} posts.");

        // ── Reactions ──
        $this->command->info('Adding reactions...');
        $reactionTypes = ['like', 'love', 'fire', 'wow', 'congrats'];
        $reactionCount = 0;

        foreach ($posts as $post) {
            // Each post gets 2-15 random reactions
            $reactors = $users->random(min(rand(2, 15), $users->count()));
            foreach ($reactors as $reactor) {
                if ($reactor->id === $post->user_id) continue; // don't react to own post

                Reaction::firstOrCreate([
                    'post_id' => $post->id,
                    'user_id' => $reactor->id,
                ], [
                    'type' => $reactionTypes[array_rand($reactionTypes)],
                ]);
                $reactionCount++;
            }
        }

        $this->command->info("Created {$reactionCount} reactions.");

        // ── Comments ──
        $this->command->info('Adding comments...');
        $commentTemplates = [
            'Ganda! 😍', 'Sana all! 🥺', 'Ang galing! 💪', 'Next time sama ako! 🙋',
            'Grabe ang view! 🏔️', 'Bucket list ko to! ✅', 'How much budget? 💰',
            'Paano pumunta dyan? 🗺️', 'Solid! 🔥', 'Level up! ⚡',
            'Congrats! 🎉', 'Inggit ako! 😭', 'Sama next time! 🤙',
            'Worth it ba? 🤔', 'Ang saya naman! 😊', 'Dream destination ko yan! 💭',
            'Kailan ulit? 📅', 'Nice shot! 📸', 'Goals! 🎯', 'Respect! 🫡',
            'Dami mo na na-unlock! 🔓', 'Pogi/Ganda ng place! ✨', 'Saan exact location? 📍',
            'Mahirap ba trail? 🥾', 'Ang linaw ng tubig! 💧',
        ];

        $replyTemplates = [
            'Thank you! 😊', 'Tara next time! 🤙', 'Oo sobra! 💯',
            'Around 3-5k budget 💰', 'Message mo ko for details! 📩',
            'Haha salamat! 😂', 'G! Set na! 🔥', 'Oo worth it talaga! ✅',
        ];

        $commentCount = 0;
        foreach ($posts as $post) {
            // Each post gets 1-8 comments
            $numComments = rand(1, 8);
            $commenters = $users->random(min($numComments, $users->count()));

            foreach ($commenters as $commenter) {
                if ($commenter->id === $post->user_id && rand(0, 1)) continue;

                $comment = Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $commenter->id,
                    'content' => $commentTemplates[array_rand($commentTemplates)],
                    'created_at' => $post->created_at->addMinutes(rand(5, 1440)),
                ]);
                $commentCount++;

                // 40% chance of a reply from the post author
                if (rand(1, 100) <= 40) {
                    Comment::create([
                        'post_id' => $post->id,
                        'user_id' => $post->user_id,
                        'content' => $replyTemplates[array_rand($replyTemplates)],
                        'parent_id' => $comment->id,
                        'created_at' => $comment->created_at->addMinutes(rand(2, 120)),
                    ]);
                    $commentCount++;
                }
            }
        }

        $this->command->info("Created {$commentCount} comments.");

        // ── Follow relationships (for feed algo) ──
        $this->command->info('Creating follow relationships...');
        $followCount = 0;
        foreach ($users as $user) {
            // Each user follows 3-10 random users
            $toFollow = $users->where('id', '!=', $user->id)->random(min(rand(3, 10), $users->count() - 1));
            foreach ($toFollow as $target) {
                try {
                    \DB::table('follows')->insertOrIgnore([
                        'follower_id' => $user->id,
                        'following_id' => $target->id,
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(1, 30)),
                    ]);
                    $followCount++;
                } catch (\Throwable $e) {
                    // skip duplicates
                }
            }
        }

        $this->command->info("Created {$followCount} follow relationships.");
        $this->command->info('✅ CommunitySeeder done.');
    }
}
