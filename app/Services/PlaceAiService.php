<?php

namespace App\Services;

use App\Enums\PlaceCategory;
use App\Models\Place;
use App\Models\PlaceImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaceAiService
{
    private string $geminiKey;
    private string $unsplashKey;
    private string $pexelsKey;

    public function __construct()
    {
        $this->geminiKey = config('services.gemini.key', env('GEMINI_API_KEY', ''));
        $this->unsplashKey = config('services.unsplash.key', env('UNSPLASH_ACCESS_KEY', ''));
        $this->pexelsKey = config('services.pexels.key', env('PEXELS_API_KEY', ''));
    }

    /**
     * Discover new Philippine travel places using Gemini AI.
     * Returns array of place data ready to insert.
     */
    public function discoverNewPlaces(int $count = 10, ?string $category = null): array
    {
        $existingNames = Place::pluck('name')->implode(', ');
        $categoryFilter = $category ? "Category: {$category} only." : 'Mix of all categories.';

        $prompt = "You are a Philippine travel expert and content writer. Generate exactly {$count} real travel destinations in the Philippines that are NOT in this list: [{$existingNames}].

{$categoryFilter}

Categories: mountain, beach, island, falls, river, lake, campsite, historical, food_destination, road_trip, hidden_gem

For each place return a JSON array with objects containing:
- name: official place name (use the most commonly known name)
- category: one of the categories above
- description: Write a detailed, engaging 5-8 sentence description. Include: what makes it special, what visitors can expect, the landscape/scenery, best time to visit, any unique features or history. Write like a travel blog — informative but exciting.
- region: Philippine region — must use the FULL official name from this list: National Capital Region (NCR), Cordillera Administrative Region (CAR), Region I — Ilocos Region, Region II — Cagayan Valley, Region III — Central Luzon, Region IV-A — CALABARZON, MIMAROPA Region, Region V — Bicol Region, Region VI — Western Visayas, Region VII — Central Visayas, Region VIII — Eastern Visayas, Region IX — Zamboanga Peninsula, Region X — Northern Mindanao, Region XI — Davao Region, Region XII — SOCCSKSARGEN, Region XIII — Caraga, Bangsamoro (BARMM)
- province: Philippine province (exact name)
- latitude: decimal (accurate to 4 decimal places, must be real coordinates)
- longitude: decimal (accurate to 4 decimal places, must be real coordinates)
- xp_reward: integer 30-250 based on difficulty and remoteness (harder to reach = more XP)
- meta: object with category-specific fields:
  For mountain: { elevation_masl, trail_class (1-9), difficulty_label (easy/moderate/hard/extreme), trail_type (day_hike/overnight/multi_day/traverse), estimated_hours, jump_off_point, permit_required (yes/no), guide_required (yes/no) }
  For beach: { sand_type (white/cream/golden/black/pink/pebble/rocky), water_activity, entrance_fee, best_season, accessibility (easy/moderate/hard) }
  For island: { how_to_get_there, boat_ride_minutes, overnight_allowed (yes/no), entrance_fee, best_season }
  For falls: { height_meters, layers, swimming_allowed (yes/no), trek_minutes, entrance_fee, difficulty_label (easy/moderate/hard) }
  For river: { activity_type, rapids_class (I-V), length_km, best_season }
  For lake: { elevation_masl, swimming_allowed (yes/no), boat_allowed (yes/no), trek_required (yes/no), entrance_fee }
  For campsite: { campsite_type (beach/mountain/forest/lakeside/riverside), tent_rental (yes/no), facilities, camping_fee, signal_available (yes/weak/no) }
  For historical: { historical_period, year_built, heritage_status (none/local/national/unesco), entrance_fee, guided_tour (yes/no) }
  For food_destination: { cuisine_type, must_try, price_range (budget/mid/premium), food_type (restaurant/street_food/market/food_park/cafe) }
  For road_trip: { distance_km, drive_hours, route_highlights, road_condition (paved/mixed/rough), vehicle_type (any/suv/motorcycle) }
  For hidden_gem: { discovery_tip, crowd_level (empty/few/moderate/crowded), best_time, entrance_fee }

Return ONLY valid JSON array, no markdown, no explanation.";

        try {
            $response = Http::timeout(60)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->geminiKey}",
                [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 4096],
                ]
            );

            if (!$response->successful()) {
                Log::error('Gemini API failed', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            $text = $response->json('candidates.0.content.parts.0.text', '');
            // Clean markdown code blocks if present
            $text = preg_replace('/```json?\s*/', '', $text);
            $text = preg_replace('/```\s*/', '', $text);
            $text = trim($text);

            $places = json_decode($text, true);
            if (!is_array($places)) {
                Log::error('Gemini returned invalid JSON', ['text' => substr($text, 0, 500)]);
                return [];
            }

            return $places;
        } catch (\Throwable $e) {
            Log::error('Gemini discover failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Update a place's details using Gemini AI (verify/correct data).
     */
    public function updatePlaceDetails(Place $place): bool
    {
        $categoryMeta = config("place_fields.{$place->category->value}", []);
        $metaKeys = collect($categoryMeta)->pluck('key')->implode(', ');

        $prompt = "You are a Philippine travel expert and content writer. Verify and enhance the details for this place:

Name: {$place->name}
Category: {$place->category->value}
Region: {$place->region}
Province: {$place->province}
Current Description: {$place->description}
Latitude: {$place->latitude}
Longitude: {$place->longitude}

Return a JSON object with corrected/enhanced fields:
- description: Write a detailed, engaging 5-8 sentence description. Include what makes it special, what visitors can expect, the landscape/scenery, best time to visit, unique features or history. Write like a travel blog — informative but exciting. Do NOT repeat the current description if it's generic.
- region: correct full Philippine region name
- province: correct Philippine province name
- latitude: accurate decimal (4 decimal places, must be real coordinates for this place)
- longitude: accurate decimal (4 decimal places, must be real coordinates for this place)
- xp_reward: integer 30-250 based on difficulty/remoteness
- meta: object with these category-specific fields for {$place->category->value}: {$metaKeys}
  Fill in accurate values for each field. Use null for unknown fields.

Return ONLY valid JSON object, no markdown.";

        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->geminiKey}",
                [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 1024],
                ]
            );

            if (!$response->successful()) return false;

            $text = $response->json('candidates.0.content.parts.0.text', '');
            $text = preg_replace('/```json?\s*/', '', $text);
            $text = preg_replace('/```\s*/', '', $text);
            $data = json_decode(trim($text), true);

            if (!is_array($data)) return false;

            $updates = [];
            if (!empty($data['description'])) $updates['description'] = $data['description'];
            if (!empty($data['region'])) $updates['region'] = $data['region'];
            if (!empty($data['province'])) $updates['province'] = $data['province'];
            if (!empty($data['latitude'])) $updates['latitude'] = $data['latitude'];
            if (!empty($data['longitude'])) $updates['longitude'] = $data['longitude'];
            if (!empty($data['xp_reward']) && !$place->xp_reward) $updates['xp_reward'] = $data['xp_reward'];

            if (!empty($updates)) {
                $place->update($updates);
                Log::info("AI updated place: {$place->name}", $updates);
            }

            // Save category-specific meta
            if (!empty($data['meta']) && is_array($data['meta'])) {
                $place->syncMeta(array_filter($data['meta'], function ($v) {
                    return $v !== null && $v !== '';
                }));
                Log::info("AI updated meta for {$place->name}", array_keys($data['meta']));
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("AI update failed for {$place->name}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Fetch a photo for a place — tries Unsplash first, falls back to Pexels.
     */
    public function fetchAndUploadPhoto(Place $place): ?string
    {
        // Try Pexels first (easier setup, 200 req/hr)
        if ($this->pexelsKey) {
            $result = $this->fetchFromPexels($place);
            if ($result) return $result;
        }

        // Fallback to Unsplash
        if ($this->unsplashKey) {
            $result = $this->fetchFromUnsplash($place);
            if ($result) return $result;
        }

        Log::warning("No photo found for {$place->name} from any source");
        return null;
    }

    private function fetchFromUnsplash(Place $place): ?string
    {
        $query = "{$place->name} Philippines {$place->category->value}";

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => "Client-ID {$this->unsplashKey}"])
                ->get('https://api.unsplash.com/search/photos', [
                    'query' => $query,
                    'per_page' => 1,
                    'orientation' => 'landscape',
                ]);

            if (!$response->successful()) return null;

            $results = $response->json('results', []);
            if (empty($results)) {
                // Simpler query fallback
                $response = Http::timeout(15)
                    ->withHeaders(['Authorization' => "Client-ID {$this->unsplashKey}"])
                    ->get('https://api.unsplash.com/search/photos', [
                        'query' => "{$place->name} Philippines",
                        'per_page' => 1,
                        'orientation' => 'landscape',
                    ]);
                $results = $response->json('results', []);
            }

            if (empty($results)) return null;

            $imageUrl = $results[0]['urls']['regular'] ?? null;
            if (!$imageUrl) return null;

            return $this->downloadAndSave($place, $imageUrl, 'unsplash', $results[0]['description'] ?? $results[0]['alt_description'] ?? null);
        } catch (\Throwable $e) {
            Log::error("Unsplash failed for {$place->name}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function fetchFromPexels(Place $place): ?string
    {
        $query = "{$place->name} Philippines";

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => $this->pexelsKey])
                ->get('https://api.pexels.com/v1/search', [
                    'query' => $query,
                    'per_page' => 1,
                    'orientation' => 'landscape',
                ]);

            if (!$response->successful()) return null;

            $photos = $response->json('photos', []);
            if (empty($photos)) return null;

            $imageUrl = $photos[0]['src']['large'] ?? $photos[0]['src']['original'] ?? null;
            if (!$imageUrl) return null;

            return $this->downloadAndSave($place, $imageUrl, 'pexels', $photos[0]['alt'] ?? null);
        } catch (\Throwable $e) {
            Log::error("Pexels failed for {$place->name}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function downloadAndSave(Place $place, string $imageUrl, string $source, ?string $caption): ?string
    {
        try {
            $imageContent = Http::timeout(30)->get($imageUrl)->body();
            $filename = 'place-gallery/' . Str::random(40) . '.jpg';
            Storage::disk('s3')->put($filename, $imageContent);

            PlaceImage::create([
                'place_id' => $place->id,
                'image_path' => $filename,
                'image_source' => $source,
                'caption' => $caption,
                'is_cover' => !$place->cover_image_path,
                'sort_order' => 0,
            ]);

            if (!$place->cover_image_path) {
                $place->update(['cover_image_path' => $filename]);
            }

            Log::info("Photo saved for {$place->name}", ['source' => $source]);
            return $filename;
        } catch (\Throwable $e) {
            Log::error("Photo download failed for {$place->name}", ['error' => $e->getMessage()]);
            return null;
        }
    }
}
