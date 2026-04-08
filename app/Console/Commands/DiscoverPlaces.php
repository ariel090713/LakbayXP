<?php

namespace App\Console\Commands;

use App\Enums\PlaceCategory;
use App\Models\Place;
use App\Services\PlaceAiService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DiscoverPlaces extends Command
{
    protected $signature = 'places:discover {--count=10 : Number of places to discover} {--category= : Specific category} {--with-photos : Also fetch photos}';
    protected $description = 'Use AI to discover new Philippine travel places and add them to the database';

    public function handle(PlaceAiService $aiService): int
    {
        $count = (int) $this->option('count');
        $category = $this->option('category');
        $withPhotos = $this->option('with-photos');

        $this->info("🔍 Discovering {$count} new places" . ($category ? " ({$category})" : '') . '...');

        $places = $aiService->discoverNewPlaces($count, $category);

        if (empty($places)) {
            $this->error('No places returned from AI.');
            return 1;
        }

        $created = 0;
        foreach ($places as $p) {
            $name = $p['name'] ?? null;
            if (!$name) continue;

            // Skip if already exists
            $slug = Str::slug($name);
            if (Place::where('slug', $slug)->orWhere('name', $name)->exists()) {
                $this->line("  ⏭️  {$name} (already exists)");
                continue;
            }

            $cat = PlaceCategory::tryFrom($p['category'] ?? 'hidden_gem');
            if (!$cat) $cat = PlaceCategory::HiddenGem;

            $place = Place::create([
                'name' => $name,
                'slug' => $slug,
                'description' => $p['description'] ?? "A beautiful destination in the Philippines.",
                'category' => $cat,
                'region' => $p['region'] ?? null,
                'province' => $p['province'] ?? null,
                'latitude' => $p['latitude'] ?? null,
                'longitude' => $p['longitude'] ?? null,
                'xp_reward' => $p['xp_reward'] ?? 50,
                'points_reward' => $p['points_reward'] ?? intval(($p['xp_reward'] ?? 50) * 0.5),
                'is_active' => true,
                'created_by' => 1,
            ]);

            // Save extra meta from AI (category-specific)
            if (!empty($p['meta']) && is_array($p['meta'])) {
                $place->syncMeta(array_filter($p['meta'], function ($v) {
                    return $v !== null && $v !== '';
                }));
            }

            $this->info("  ✅ {$name} ({$cat->value})");
            $created++;

            if ($withPhotos) {
                $this->line("     📸 Fetching photo...");
                $aiService->fetchAndUploadPhoto($place);
                sleep(1); // Rate limit
            }
        }

        $this->info("✅ Done. Created {$created} new places.");
        return 0;
    }
}
