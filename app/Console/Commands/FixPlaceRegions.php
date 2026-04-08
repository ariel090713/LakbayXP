<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPlaceRegions extends Command
{
    protected $signature = 'places:fix-regions';
    protected $description = 'Fix place region/province names to match the regions/provinces tables';

    public function handle(): int
    {
        $regions = DB::table('regions')->get();
        $provinces = DB::table('provinces')->get();

        // Build a mapping of short names to full names
        $regionMap = [
            'NCR' => 'National Capital Region (NCR)',
            'Cordillera' => 'Cordillera Administrative Region (CAR)',
            'CAR' => 'Cordillera Administrative Region (CAR)',
            'Ilocos' => 'Region I — Ilocos Region',
            'Cagayan Valley' => 'Region II — Cagayan Valley',
            'Central Luzon' => 'Region III — Central Luzon',
            'CALABARZON' => 'Region IV-A — CALABARZON',
            'MIMAROPA' => 'MIMAROPA Region',
            'Bicol' => 'Region V — Bicol Region',
            'Western Visayas' => 'Region VI — Western Visayas',
            'Central Visayas' => 'Region VII — Central Visayas',
            'Eastern Visayas' => 'Region VIII — Eastern Visayas',
            'Zamboanga Peninsula' => 'Region IX — Zamboanga Peninsula',
            'Zamboanga' => 'Region IX — Zamboanga Peninsula',
            'Northern Mindanao' => 'Region X — Northern Mindanao',
            'Davao' => 'Region XI — Davao Region',
            'Davao Region' => 'Region XI — Davao Region',
            'SOCCSKSARGEN' => 'Region XII — SOCCSKSARGEN',
            'Caraga' => 'Region XIII — Caraga',
            'BARMM' => 'Bangsamoro (BARMM)',
            'Bangsamoro' => 'Bangsamoro (BARMM)',
        ];

        $fixed = 0;
        $places = Place::all();

        foreach ($places as $place) {
            $updates = [];

            // Fix region
            if ($place->region && isset($regionMap[$place->region])) {
                $fullName = $regionMap[$place->region];
                if ($fullName !== $place->region) {
                    $updates['region'] = $fullName;
                }
            }

            // Fix province casing
            if ($place->province) {
                $match = $provinces->first(fn($p) => strtolower($p->name) === strtolower($place->province));
                if ($match && $match->name !== $place->province) {
                    $updates['province'] = $match->name;
                }
            }

            if (!empty($updates)) {
                $place->update($updates);
                $this->line("  ✅ {$place->name}: " . implode(', ', array_map(fn($k, $v) => "{$k} → {$v}", array_keys($updates), $updates)));
                $fixed++;
            }
        }

        $this->info("Fixed {$fixed} places.");
        return 0;
    }
}
