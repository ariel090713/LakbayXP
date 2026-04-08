<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => 'NCR', 'name' => 'National Capital Region (NCR)', 'provinces' => [
                'Manila', 'Quezon City', 'Caloocan', 'Las Piñas', 'Makati',
                'Malabon', 'Mandaluyong', 'Marikina', 'Muntinlupa', 'Navotas',
                'Parañaque', 'Pasay', 'Pasig', 'San Juan', 'Taguig', 'Valenzuela', 'Pateros',
            ]],
            ['code' => 'CAR', 'name' => 'Cordillera Administrative Region (CAR)', 'provinces' => [
                'Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province',
            ]],
            ['code' => 'I', 'name' => 'Region I — Ilocos Region', 'provinces' => [
                'Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan',
            ]],
            ['code' => 'II', 'name' => 'Region II — Cagayan Valley', 'provinces' => [
                'Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino',
            ]],
            ['code' => 'III', 'name' => 'Region III — Central Luzon', 'provinces' => [
                'Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales',
            ]],
            ['code' => 'IV-A', 'name' => 'Region IV-A — CALABARZON', 'provinces' => [
                'Batangas', 'Cavite', 'Laguna', 'Quezon', 'Rizal',
            ]],
            ['code' => 'MIMAROPA', 'name' => 'MIMAROPA Region', 'provinces' => [
                'Marinduque', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Romblon',
            ]],
            ['code' => 'V', 'name' => 'Region V — Bicol Region', 'provinces' => [
                'Albay', 'Camarines Norte', 'Camarines Sur', 'Catanduanes', 'Masbate', 'Sorsogon',
            ]],
            ['code' => 'VI', 'name' => 'Region VI — Western Visayas', 'provinces' => [
                'Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental',
            ]],
            ['code' => 'VII', 'name' => 'Region VII — Central Visayas', 'provinces' => [
                'Bohol', 'Cebu', 'Negros Oriental', 'Siquijor',
            ]],
            ['code' => 'VIII', 'name' => 'Region VIII — Eastern Visayas', 'provinces' => [
                'Biliran', 'Eastern Samar', 'Leyte', 'Northern Samar', 'Samar', 'Southern Leyte',
            ]],
            ['code' => 'IX', 'name' => 'Region IX — Zamboanga Peninsula', 'provinces' => [
                'Zamboanga del Norte', 'Zamboanga del Sur', 'Zamboanga Sibugay', 'Zamboanga City',
            ]],
            ['code' => 'X', 'name' => 'Region X — Northern Mindanao', 'provinces' => [
                'Bukidnon', 'Camiguin', 'Lanao del Norte', 'Misamis Occidental', 'Misamis Oriental',
                'Cagayan de Oro',
            ]],
            ['code' => 'XI', 'name' => 'Region XI — Davao Region', 'provinces' => [
                'Davao de Oro', 'Davao del Norte', 'Davao del Sur', 'Davao Occidental', 'Davao Oriental',
                'Davao City',
            ]],
            ['code' => 'XII', 'name' => 'Region XII — SOCCSKSARGEN', 'provinces' => [
                'Cotabato', 'Sarangani', 'South Cotabato', 'Sultan Kudarat',
                'General Santos', 'Cotabato City',
            ]],
            ['code' => 'XIII', 'name' => 'Region XIII — Caraga', 'provinces' => [
                'Agusan del Norte', 'Agusan del Sur', 'Dinagat Islands', 'Surigao del Norte', 'Surigao del Sur',
                'Butuan',
            ]],
            ['code' => 'BARMM', 'name' => 'Bangsamoro (BARMM)', 'provinces' => [
                'Basilan', 'Lanao del Sur', 'Maguindanao del Norte', 'Maguindanao del Sur',
                'Sulu', 'Tawi-Tawi', 'Cotabato City',
            ]],
        ];

        foreach ($data as $order => $region) {
            // Insert region if not exists
            $existing = DB::table('regions')->where('code', $region['code'])->first();

            if ($existing) {
                $regionId = $existing->id;
            } else {
                $regionId = DB::table('regions')->insertGetId([
                    'name' => $region['name'],
                    'code' => $region['code'],
                    'sort_order' => $order + 1,
                ]);
            }

            // Insert provinces if not exists
            foreach ($region['provinces'] as $pOrder => $province) {
                $exists = DB::table('provinces')
                    ->where('region_id', $regionId)
                    ->where('name', $province)
                    ->exists();

                if (!$exists) {
                    DB::table('provinces')->insert([
                        'region_id' => $regionId,
                        'name' => $province,
                        'sort_order' => $pOrder + 1,
                    ]);
                }
            }
        }
    }
}
