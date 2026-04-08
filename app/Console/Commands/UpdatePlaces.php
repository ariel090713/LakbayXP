<?php

namespace App\Console\Commands;

use App\Models\Place;
use App\Services\PlaceAiService;
use Illuminate\Console\Command;

class UpdatePlaces extends Command
{
    protected $signature = 'places:update {--limit=20 : Number of places to update per run} {--photos : Also fetch missing photos} {--force : Update even if already has description}';
    protected $description = 'Use AI to verify/correct place details and fetch missing photos';

    public function handle(PlaceAiService $aiService): int
    {
        $limit = (int) $this->option('limit');
        $fetchPhotos = $this->option('photos');
        $force = $this->option('force');

        // Prioritize places with missing data
        $query = Place::where('is_active', true);

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('description')
                  ->orWhere('description', '')
                  ->orWhere('description', 'like', 'A beautiful%') // default seeder description
                  ->orWhereNull('latitude')
                  ->orWhereNull('longitude')
                  ->orWhere('xp_reward', 0);
            });
        }

        $places = $query->orderBy('updated_at')->take($limit)->get();

        if ($places->isEmpty()) {
            // If no incomplete places, update oldest ones
            $places = Place::where('is_active', true)
                ->orderBy('updated_at')
                ->take($limit)
                ->get();
        }

        $this->info("📝 Updating {$places->count()} places...");

        $updated = 0;
        $photosAdded = 0;

        foreach ($places as $place) {
            $this->line("  🔄 {$place->name}...");

            if ($aiService->updatePlaceDetails($place)) {
                $updated++;
                $this->info("     ✅ Details updated");
            }

            // Fetch photo if missing
            if ($fetchPhotos && !$place->cover_image_path && $place->images()->count() === 0) {
                $this->line("     📸 Fetching photo...");
                if ($aiService->fetchAndUploadPhoto($place)) {
                    $photosAdded++;
                }
                sleep(1); // Rate limit Unsplash
            }

            sleep(1); // Rate limit Gemini
        }

        $this->info("✅ Done. Updated {$updated} places, added {$photosAdded} photos.");
        return 0;
    }
}
