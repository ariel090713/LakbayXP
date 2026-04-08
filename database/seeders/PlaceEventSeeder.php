<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\PlaceCategory;
use App\Enums\UnlockMethod;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventPlace;
use App\Models\EventRule;
use App\Models\Place;
use App\Models\PlaceUnlock;
use App\Models\User;
use App\Services\XpService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlaceEventSeeder extends Seeder
{
    public function run(): void
    {
        $xpService = app(XpService::class);

        // ─── Organizers ───────────────────────────────────────────────────────
        $organizers = $this->seedOrganizers();

        // ─── Explorer Users ───────────────────────────────────────────────────
        $users = $this->seedUsers(40);

        // ─── Places (100+) ────────────────────────────────────────────────────
        $places = $this->seedPlaces($organizers[0]);

        // ─── Events with Itinerary & Rules ────────────────────────────────────
        $events = $this->seedEvents($organizers, $places);

        // ─── Bookings & Joiners ───────────────────────────────────────────────
        $this->seedBookings($events, $users);

        // ─── Place Unlocks & XP ───────────────────────────────────────────────
        $this->seedUnlocks($users, $places, $xpService);

        $this->command->info('✅ PlaceEventSeeder done — ' . $places->count() . ' places, ' . $events->count() . ' events, ' . $users->count() . ' users.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function seedOrganizers(): array
    {
        $data = [
            ['name' => 'Lakbay Guides PH', 'email' => 'lakbay@guides.ph', 'type' => 'agency', 'specialties' => ['mountain', 'campsite']],
            ['name' => 'Island Hoppers PH', 'email' => 'island@hoppers.ph', 'type' => 'agency', 'specialties' => ['beach', 'island']],
            ['name' => 'Juan dela Cruz', 'email' => 'juan@explorer.ph', 'type' => 'solo', 'specialties' => ['mountain', 'falls', 'river']],
            ['name' => 'Visayas Wanderers', 'email' => 'visayas@wanderers.ph', 'type' => 'organization', 'specialties' => ['beach', 'historical', 'food_destination']],
        ];

        return collect($data)->map(function ($d) {
            return User::firstOrCreate(['email' => $d['email']], [
                'name' => $d['name'],
                'username' => Str::slug($d['name']) . '-' . Str::random(3),
                'password' => Hash::make('password'),
                'role' => UserRole::Organizer,
                'is_verified_organizer' => true,
                'onboarding_completed' => true,
                'organizer_type' => $d['type'],
                'specialties' => $d['specialties'],
            ]);
        })->all();
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function seedUsers(int $count): \Illuminate\Support\Collection
    {
        $names = [
            'Maria Santos', 'Jose Reyes', 'Ana Garcia', 'Pedro Dela Cruz', 'Rosa Mendoza',
            'Carlo Bautista', 'Liza Aquino', 'Mark Villanueva', 'Cris Fernandez', 'Joy Ramos',
            'Nico Castillo', 'Bea Torres', 'Renz Flores', 'Mia Gonzales', 'Gio Morales',
            'Tina Navarro', 'Kiko Pascual', 'Lea Soriano', 'Dino Aguilar', 'Pia Salazar',
            'Raf Espiritu', 'Gab Ocampo', 'Kat Lim', 'Jed Tan', 'Lyn Uy',
            'Vince Sy', 'Trish Go', 'Alec Chua', 'Migs Yap', 'Cess Ang',
            'Enzo Dela Rosa', 'Ria Macaraeg', 'Bong Ilagan', 'Nena Buenaventura', 'Toto Macapagal',
            'Lito Estrada', 'Nora Aunor', 'Dolphy Jr', 'Vilma Santos', 'Sharon Cuneta',
        ];

        return collect($names)->take($count)->map(function ($name, $i) {
            $email = Str::slug($name) . $i . '@explorer.ph';
            return User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'username' => Str::slug($name) . '-' . ($i + 1),
                'password' => Hash::make('password'),
                'role' => UserRole::User,
                'xp' => 0,
                'level' => 1,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function seedPlaces($admin): \Illuminate\Support\Collection
    {
        $places = collect();
        $allPlaces = $this->getPlaceData();

        foreach ($allPlaces as $p) {
            $places->push(Place::updateOrCreate(['slug' => $p['slug']], [
                'name' => $p['name'],
                'slug' => $p['slug'],
                'description' => $p['desc'],
                'category' => $p['cat'],
                'region' => $p['region'],
                'province' => $p['province'],
                'latitude' => $p['lat'],
                'longitude' => $p['lng'],
                'xp_reward' => $p['xp'],
                'is_active' => true,
                'created_by' => $admin->id,
            ]));
        }

        return $places;
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function getPlaceData(): array
    {
        return [
            // ── Mountains (20) ──
            ['name'=>'Mt. Pulag','slug'=>'mt-pulag','cat'=>PlaceCategory::Mountain,'region'=>'Cordillera','province'=>'Benguet','lat'=>16.5870,'lng'=>120.8970,'xp'=>150,'desc'=>'Highest peak in Luzon, famous for its sea of clouds.'],
            ['name'=>'Mt. Apo','slug'=>'mt-apo','cat'=>PlaceCategory::Mountain,'region'=>'Davao','province'=>'Davao del Sur','lat'=>6.9876,'lng'=>125.2707,'xp'=>200,'desc'=>'The highest mountain in the Philippines at 2,954 masl.'],
            ['name'=>'Mt. Pinatubo','slug'=>'mt-pinatubo','cat'=>PlaceCategory::Mountain,'region'=>'Central Luzon','province'=>'Zambales','lat'=>15.1429,'lng'=>120.3496,'xp'=>120,'desc'=>'Famous for its crater lake formed after the 1991 eruption.'],
            ['name'=>'Mt. Ulap','slug'=>'mt-ulap','cat'=>PlaceCategory::Mountain,'region'=>'Cordillera','province'=>'Benguet','lat'=>16.4700,'lng'=>120.5800,'xp'=>80,'desc'=>'Popular day hike with grassland views in Itogon.'],
            ['name'=>'Mt. Batulao','slug'=>'mt-batulao','cat'=>PlaceCategory::Mountain,'region'=>'CALABARZON','province'=>'Batangas','lat'=>14.0500,'lng'=>120.7700,'xp'=>70,'desc'=>'Beginner-friendly mountain with scenic ridgeline.'],
            ['name'=>'Mt. Daraitan','slug'=>'mt-daraitan','cat'=>PlaceCategory::Mountain,'region'=>'CALABARZON','province'=>'Rizal','lat'=>14.7800,'lng'=>121.3200,'xp'=>90,'desc'=>'Overlooking Tinipak River with stunning limestone formations.'],
            ['name'=>'Mt. Hamiguitan','slug'=>'mt-hamiguitan','cat'=>PlaceCategory::Mountain,'region'=>'Davao','province'=>'Davao Oriental','lat'=>6.7200,'lng'=>126.1700,'xp'=>180,'desc'=>'UNESCO World Heritage Site with pygmy forest.'],
            ['name'=>'Mt. Kanlaon','slug'=>'mt-kanlaon','cat'=>PlaceCategory::Mountain,'region'=>'Western Visayas','province'=>'Negros Occidental','lat'=>10.4120,'lng'=>123.1320,'xp'=>170,'desc'=>'Active stratovolcano and highest peak in the Visayas.'],
            ['name'=>'Mt. Guiting-Guiting','slug'=>'mt-guiting-guiting','cat'=>PlaceCategory::Mountain,'region'=>'MIMAROPA','province'=>'Romblon','lat'=>12.4000,'lng'=>122.2800,'xp'=>250,'desc'=>'One of the toughest climbs in the Philippines.'],
            ['name'=>'Mt. Pico de Loro','slug'=>'mt-pico-de-loro','cat'=>PlaceCategory::Mountain,'region'=>'CALABARZON','province'=>'Cavite','lat'=>14.2200,'lng'=>120.6100,'xp'=>60,'desc'=>'Popular weekend hike with a monolith rock formation.'],
            ['name'=>'Mt. Tapulao','slug'=>'mt-tapulao','cat'=>PlaceCategory::Mountain,'region'=>'Central Luzon','province'=>'Zambales','lat'=>15.3900,'lng'=>120.1200,'xp'=>130,'desc'=>'Highest peak in Zambales with mossy forest.'],
            ['name'=>'Mt. Halcon','slug'=>'mt-halcon','cat'=>PlaceCategory::Mountain,'region'=>'MIMAROPA','province'=>'Oriental Mindoro','lat'=>12.9700,'lng'=>121.0100,'xp'=>220,'desc'=>'One of the most difficult climbs, requires permits.'],
            ['name'=>'Mt. Dulang-Dulang','slug'=>'mt-dulang-dulang','cat'=>PlaceCategory::Mountain,'region'=>'Northern Mindanao','province'=>'Bukidnon','lat'=>8.1000,'lng'=>124.9500,'xp'=>190,'desc'=>'Second highest peak in the Philippines.'],
            ['name'=>'Mt. Maculot','slug'=>'mt-maculot','cat'=>PlaceCategory::Mountain,'region'=>'CALABARZON','province'=>'Batangas','lat'=>13.9500,'lng'=>121.0500,'xp'=>50,'desc'=>'Easy hike with Rockies viewpoint overlooking Taal Lake.'],
            ['name'=>'Mt. Timbak','slug'=>'mt-timbak','cat'=>PlaceCategory::Mountain,'region'=>'Cordillera','province'=>'Benguet','lat'=>16.6100,'lng'=>120.8600,'xp'=>100,'desc'=>'Third highest peak in Luzon with pine forest trails.'],
            ['name'=>'Mt. Napulak','slug'=>'mt-napulak','cat'=>PlaceCategory::Mountain,'region'=>'Western Visayas','province'=>'Iloilo','lat'=>11.0200,'lng'=>122.5800,'xp'=>85,'desc'=>'Scenic mountain in Panay with river crossings.'],
            ['name'=>'Mt. Mandalagan','slug'=>'mt-mandalagan','cat'=>PlaceCategory::Mountain,'region'=>'Western Visayas','province'=>'Negros Occidental','lat'=>10.3500,'lng'=>123.0800,'xp'=>110,'desc'=>'Mossy forest trail in the Negros highlands.'],
            ['name'=>'Mt. Talinis','slug'=>'mt-talinis','cat'=>PlaceCategory::Mountain,'region'=>'Central Visayas','province'=>'Negros Oriental','lat'=>9.2500,'lng'=>123.2000,'xp'=>140,'desc'=>'Twin lakes and hot springs at the summit.'],
            ['name'=>'Mt. Kitanglad','slug'=>'mt-kitanglad','cat'=>PlaceCategory::Mountain,'region'=>'Northern Mindanao','province'=>'Bukidnon','lat'=>8.1300,'lng'=>124.9000,'xp'=>160,'desc'=>'Protected range with endemic species.'],
            ['name'=>'Mt. Iglit-Baco','slug'=>'mt-iglit-baco','cat'=>PlaceCategory::Mountain,'region'=>'MIMAROPA','province'=>'Occidental Mindoro','lat'=>12.7500,'lng'=>121.2000,'xp'=>175,'desc'=>'Home of the endangered tamaraw.'],

            // ── Beaches (15) ──
            ['name'=>'Boracay White Beach','slug'=>'boracay-white-beach','cat'=>PlaceCategory::Beach,'region'=>'Western Visayas','province'=>'Aklan','lat'=>11.9674,'lng'=>121.9248,'xp'=>80,'desc'=>'World-famous white sand beach.'],
            ['name'=>'Nacpan Beach','slug'=>'nacpan-beach','cat'=>PlaceCategory::Beach,'region'=>'MIMAROPA','province'=>'Palawan','lat'=>11.2800,'lng'=>119.4200,'xp'=>90,'desc'=>'Twin beach paradise in El Nido.'],
            ['name'=>'Pagudpud Blue Lagoon','slug'=>'pagudpud-blue-lagoon','cat'=>PlaceCategory::Beach,'region'=>'Ilocos','province'=>'Ilocos Norte','lat'=>18.5600,'lng'=>120.8500,'xp'=>70,'desc'=>'Crystal clear waters in the north.'],
            ['name'=>'Dahican Beach','slug'=>'dahican-beach','cat'=>PlaceCategory::Beach,'region'=>'Davao','province'=>'Davao Oriental','lat'=>7.0100,'lng'=>126.2200,'xp'=>75,'desc'=>'Surfing and skimboarding paradise.'],
            ['name'=>'Saud Beach','slug'=>'saud-beach','cat'=>PlaceCategory::Beach,'region'=>'Ilocos','province'=>'Ilocos Norte','lat'=>18.5500,'lng'=>120.8400,'xp'=>65,'desc'=>'Powdery white sand in Pagudpud.'],
            ['name'=>'Puka Shell Beach','slug'=>'puka-shell-beach','cat'=>PlaceCategory::Beach,'region'=>'Western Visayas','province'=>'Aklan','lat'=>11.9900,'lng'=>121.9100,'xp'=>60,'desc'=>'Quieter beach on Boracay north end.'],
            ['name'=>'Daku Beach','slug'=>'daku-beach','cat'=>PlaceCategory::Beach,'region'=>'Caraga','province'=>'Surigao del Norte','lat'=>9.8700,'lng'=>126.1200,'xp'=>70,'desc'=>'Beautiful island beach near Siargao.'],
            ['name'=>'Calaguas Beach','slug'=>'calaguas-beach','cat'=>PlaceCategory::Beach,'region'=>'Bicol','province'=>'Camarines Norte','lat'=>14.2800,'lng'=>122.5200,'xp'=>85,'desc'=>'Remote white sand beach in Bicol.'],
            ['name'=>'Bantayan Island Beach','slug'=>'bantayan-beach','cat'=>PlaceCategory::Beach,'region'=>'Central Visayas','province'=>'Cebu','lat'=>11.1700,'lng'=>123.7300,'xp'=>65,'desc'=>'Sugar Beach on Bantayan Island.'],
            ['name'=>'Samal Island Beach','slug'=>'samal-beach','cat'=>PlaceCategory::Beach,'region'=>'Davao','province'=>'Davao del Norte','lat'=>7.0800,'lng'=>125.7200,'xp'=>55,'desc'=>'Island getaway minutes from Davao City.'],
            ['name'=>'Jomalig Beach','slug'=>'jomalig-beach','cat'=>PlaceCategory::Beach,'region'=>'CALABARZON','province'=>'Quezon','lat'=>14.7200,'lng'=>122.4100,'xp'=>95,'desc'=>'Golden sand beach off the beaten path.'],
            ['name'=>'Anguib Beach','slug'=>'anguib-beach','cat'=>PlaceCategory::Beach,'region'=>'Cagayan Valley','province'=>'Cagayan','lat'=>18.6100,'lng'=>122.1500,'xp'=>80,'desc'=>'The Boracay of the North.'],
            ['name'=>'Gumasa Beach','slug'=>'gumasa-beach','cat'=>PlaceCategory::Beach,'region'=>'SOCCSKSARGEN','province'=>'Sarangani','lat'=>5.9500,'lng'=>125.3500,'xp'=>70,'desc'=>'Pristine beach in southern Mindanao.'],
            ['name'=>'Subic Beach','slug'=>'subic-beach-sorsogon','cat'=>PlaceCategory::Beach,'region'=>'Bicol','province'=>'Sorsogon','lat'=>12.7500,'lng'=>124.0500,'xp'=>60,'desc'=>'Pink-sand beach in Matnog.'],
            ['name'=>'Zamboanga Pink Beach','slug'=>'zamboanga-pink-beach','cat'=>PlaceCategory::Beach,'region'=>'Zamboanga Peninsula','province'=>'Zamboanga City','lat'=>6.9200,'lng'=>122.0700,'xp'=>85,'desc'=>'Rare pink sand beach in Sta. Cruz Island.'],

            // ── Islands (12) ──
            ['name'=>'Siargao Island','slug'=>'siargao-island','cat'=>PlaceCategory::Island,'region'=>'Caraga','province'=>'Surigao del Norte','lat'=>9.8482,'lng'=>126.0458,'xp'=>100,'desc'=>'Surfing capital of the Philippines.'],
            ['name'=>'El Nido','slug'=>'el-nido','cat'=>PlaceCategory::Island,'region'=>'MIMAROPA','province'=>'Palawan','lat'=>11.1784,'lng'=>119.3930,'xp'=>110,'desc'=>'Limestone cliffs and hidden lagoons.'],
            ['name'=>'Coron','slug'=>'coron','cat'=>PlaceCategory::Island,'region'=>'MIMAROPA','province'=>'Palawan','lat'=>11.9986,'lng'=>120.2043,'xp'=>120,'desc'=>'Shipwreck diving and pristine lakes.'],
            ['name'=>'Batanes','slug'=>'batanes','cat'=>PlaceCategory::Island,'region'=>'Cagayan Valley','province'=>'Batanes','lat'=>20.4487,'lng'=>121.9710,'xp'=>150,'desc'=>'Northernmost province with rolling hills.'],
            ['name'=>'Camiguin Island','slug'=>'camiguin-island','cat'=>PlaceCategory::Island,'region'=>'Northern Mindanao','province'=>'Camiguin','lat'=>9.1700,'lng'=>124.7200,'xp'=>90,'desc'=>'Island born of fire with more volcanoes per sq km than any other.'],
            ['name'=>'Apo Island','slug'=>'apo-island','cat'=>PlaceCategory::Island,'region'=>'Central Visayas','province'=>'Negros Oriental','lat'=>9.0700,'lng'=>123.2700,'xp'=>80,'desc'=>'World-class diving and turtle sanctuary.'],
            ['name'=>'Caramoan Islands','slug'=>'caramoan-islands','cat'=>PlaceCategory::Island,'region'=>'Bicol','province'=>'Camarines Sur','lat'=>13.7700,'lng'=>123.8600,'xp'=>95,'desc'=>'Survivor TV show filming location.'],
            ['name'=>'Hundred Islands','slug'=>'hundred-islands','cat'=>PlaceCategory::Island,'region'=>'Ilocos','province'=>'Pangasinan','lat'=>16.2000,'lng'=>119.9500,'xp'=>60,'desc'=>'National park with 124 islands.'],
            ['name'=>'Siquijor Island','slug'=>'siquijor-island','cat'=>PlaceCategory::Island,'region'=>'Central Visayas','province'=>'Siquijor','lat'=>9.2000,'lng'=>123.5100,'xp'=>85,'desc'=>'Mystical island with enchanting falls.'],
            ['name'=>'Balabac Islands','slug'=>'balabac-islands','cat'=>PlaceCategory::Island,'region'=>'MIMAROPA','province'=>'Palawan','lat'=>7.9800,'lng'=>117.0500,'xp'=>140,'desc'=>'Southernmost tip of Palawan, untouched paradise.'],
            ['name'=>'Kalanggaman Island','slug'=>'kalanggaman-island','cat'=>PlaceCategory::Island,'region'=>'Eastern Visayas','province'=>'Leyte','lat'=>11.2200,'lng'=>124.2800,'xp'=>75,'desc'=>'Famous sandbar island.'],
            ['name'=>'Palaui Island','slug'=>'palaui-island','cat'=>PlaceCategory::Island,'region'=>'Cagayan Valley','province'=>'Cagayan','lat'=>18.5400,'lng'=>122.3700,'xp'=>100,'desc'=>'Remote island with Cape Engaño lighthouse.'],

            // ── Waterfalls (10) ──
            ['name'=>'Kawasan Falls','slug'=>'kawasan-falls','cat'=>PlaceCategory::Falls,'region'=>'Central Visayas','province'=>'Cebu','lat'=>9.8100,'lng'=>123.3700,'xp'=>80,'desc'=>'Multi-tiered turquoise waterfall in Badian.'],
            ['name'=>'Pagsanjan Falls','slug'=>'pagsanjan-falls','cat'=>PlaceCategory::Falls,'region'=>'CALABARZON','province'=>'Laguna','lat'=>14.2300,'lng'=>121.4500,'xp'=>70,'desc'=>'Iconic falls reached by shooting the rapids.'],
            ['name'=>'Tinago Falls','slug'=>'tinago-falls','cat'=>PlaceCategory::Falls,'region'=>'Northern Mindanao','province'=>'Lanao del Norte','lat'=>8.1500,'lng'=>124.2800,'xp'=>90,'desc'=>'Hidden falls inside a deep ravine.'],
            ['name'=>'Tumalog Falls','slug'=>'tumalog-falls','cat'=>PlaceCategory::Falls,'region'=>'Central Visayas','province'=>'Cebu','lat'=>9.4600,'lng'=>123.3900,'xp'=>60,'desc'=>'Curtain-like waterfall near Oslob.'],
            ['name'=>'Mag-aso Falls','slug'=>'mag-aso-falls','cat'=>PlaceCategory::Falls,'region'=>'Central Visayas','province'=>'Bohol','lat'=>9.7200,'lng'=>124.0100,'xp'=>55,'desc'=>'Misty falls in Antequera, Bohol.'],
            ['name'=>'Ditumabo Falls','slug'=>'ditumabo-falls','cat'=>PlaceCategory::Falls,'region'=>'Cagayan Valley','province'=>'Aurora','lat'=>15.7800,'lng'=>121.6200,'xp'=>75,'desc'=>'Mother Falls — tallest in Aurora.'],
            ['name'=>'Asik-Asik Falls','slug'=>'asik-asik-falls','cat'=>PlaceCategory::Falls,'region'=>'SOCCSKSARGEN','province'=>'North Cotabato','lat'=>7.2500,'lng'=>124.8500,'xp'=>100,'desc'=>'Curtain waterfall emerging from a cliff.'],
            ['name'=>'Cambugahay Falls','slug'=>'cambugahay-falls','cat'=>PlaceCategory::Falls,'region'=>'Central Visayas','province'=>'Siquijor','lat'=>9.1800,'lng'=>123.5300,'xp'=>65,'desc'=>'Three-tiered falls with rope swings.'],
            ['name'=>'Katibawasan Falls','slug'=>'katibawasan-falls','cat'=>PlaceCategory::Falls,'region'=>'Northern Mindanao','province'=>'Camiguin','lat'=>9.2100,'lng'=>124.7400,'xp'=>60,'desc'=>'70-meter waterfall surrounded by orchids.'],
            ['name'=>'Aliwagwag Falls','slug'=>'aliwagwag-falls','cat'=>PlaceCategory::Falls,'region'=>'Davao','province'=>'Davao Oriental','lat'=>7.5200,'lng'=>126.3800,'xp'=>110,'desc'=>'Tallest curtain waterfall in the Philippines.'],

            // ── Rivers & Lakes (8) ──
            ['name'=>'Chico River','slug'=>'chico-river','cat'=>PlaceCategory::River,'region'=>'Cordillera','province'=>'Kalinga','lat'=>17.4700,'lng'=>121.1200,'xp'=>90,'desc'=>'Premier whitewater rafting destination.'],
            ['name'=>'Cagayan River','slug'=>'cagayan-river','cat'=>PlaceCategory::River,'region'=>'Cagayan Valley','province'=>'Cagayan','lat'=>17.6200,'lng'=>121.7200,'xp'=>70,'desc'=>'Longest river in the Philippines.'],
            ['name'=>'Enchanted River','slug'=>'enchanted-river','cat'=>PlaceCategory::River,'region'=>'Caraga','province'=>'Surigao del Sur','lat'=>8.1200,'lng'=>126.2000,'xp'=>85,'desc'=>'Crystal clear deep blue river in Hinatuan.'],
            ['name'=>'Lake Holon','slug'=>'lake-holon','cat'=>PlaceCategory::Lake,'region'=>'SOCCSKSARGEN','province'=>'South Cotabato','lat'=>6.2500,'lng'=>124.7800,'xp'=>120,'desc'=>'Crater lake atop Mt. Parker.'],
            ['name'=>'Lake Sebu','slug'=>'lake-sebu','cat'=>PlaceCategory::Lake,'region'=>'SOCCSKSARGEN','province'=>'South Cotabato','lat'=>6.2000,'lng'=>124.7200,'xp'=>80,'desc'=>'T\'boli cultural lake with seven falls.'],
            ['name'=>'Taal Lake','slug'=>'taal-lake','cat'=>PlaceCategory::Lake,'region'=>'CALABARZON','province'=>'Batangas','lat'=>14.0000,'lng'=>120.9900,'xp'=>60,'desc'=>'Lake within a volcano within a lake.'],
            ['name'=>'Lake Pandin','slug'=>'lake-pandin','cat'=>PlaceCategory::Lake,'region'=>'CALABARZON','province'=>'Laguna','lat'=>14.1500,'lng'=>121.5000,'xp'=>50,'desc'=>'Twin lake with bamboo raft dining.'],
            ['name'=>'Kayangan Lake','slug'=>'kayangan-lake','cat'=>PlaceCategory::Lake,'region'=>'MIMAROPA','province'=>'Palawan','lat'=>12.0100,'lng'=>120.2200,'xp'=>100,'desc'=>'Cleanest lake in the Philippines.'],

            // ── Campsites (8) ──
            ['name'=>'Anawangin Cove','slug'=>'anawangin-cove','cat'=>PlaceCategory::Campsite,'region'=>'Central Luzon','province'=>'Zambales','lat'=>14.8700,'lng'=>120.0800,'xp'=>70,'desc'=>'Pine-tree lined beach campsite.'],
            ['name'=>'Nagsasa Cove','slug'=>'nagsasa-cove','cat'=>PlaceCategory::Campsite,'region'=>'Central Luzon','province'=>'Zambales','lat'=>14.8500,'lng'=>120.0600,'xp'=>75,'desc'=>'Secluded cove with volcanic ash beach.'],
            ['name'=>'Talisayin Cove','slug'=>'talisayin-cove','cat'=>PlaceCategory::Campsite,'region'=>'Central Luzon','province'=>'Zambales','lat'=>14.8600,'lng'=>120.0700,'xp'=>65,'desc'=>'Quiet campsite between Anawangin and Nagsasa.'],
            ['name'=>'Mt. Pulag Campsite','slug'=>'mt-pulag-campsite','cat'=>PlaceCategory::Campsite,'region'=>'Cordillera','province'=>'Benguet','lat'=>16.5900,'lng'=>120.8900,'xp'=>100,'desc'=>'Camp 2 grassland campsite at 2,500 masl.'],
            ['name'=>'Masungi Georeserve','slug'=>'masungi-georeserve','cat'=>PlaceCategory::Campsite,'region'=>'CALABARZON','province'=>'Rizal','lat'=>14.5800,'lng'=>121.3200,'xp'=>80,'desc'=>'Conservation area with rope courses and hammocks.'],
            ['name'=>'Treasure Mountain','slug'=>'treasure-mountain','cat'=>PlaceCategory::Campsite,'region'=>'CALABARZON','province'=>'Rizal','lat'=>14.6200,'lng'=>121.3500,'xp'=>55,'desc'=>'Glamping site with sea of clouds.'],
            ['name'=>'Camp Sabros','slug'=>'camp-sabros','cat'=>PlaceCategory::Campsite,'region'=>'Davao','province'=>'Davao del Sur','lat'=>6.9500,'lng'=>125.3200,'xp'=>60,'desc'=>'Highland campsite near Mt. Apo.'],
            ['name'=>'Sierra Madre Campsite','slug'=>'sierra-madre-campsite','cat'=>PlaceCategory::Campsite,'region'=>'CALABARZON','province'=>'Rizal','lat'=>14.7000,'lng'=>121.3000,'xp'=>70,'desc'=>'Jungle camping in the longest mountain range.'],

            // ── Historical (8) ──
            ['name'=>'Intramuros','slug'=>'intramuros','cat'=>PlaceCategory::Historical,'region'=>'NCR','province'=>'Manila','lat'=>14.5890,'lng'=>120.9750,'xp'=>50,'desc'=>'Walled city from the Spanish colonial era.'],
            ['name'=>'Vigan Heritage Village','slug'=>'vigan-heritage','cat'=>PlaceCategory::Historical,'region'=>'Ilocos','province'=>'Ilocos Sur','lat'=>17.5747,'lng'=>120.3869,'xp'=>70,'desc'=>'UNESCO World Heritage cobblestone streets.'],
            ['name'=>'Corregidor Island','slug'=>'corregidor-island','cat'=>PlaceCategory::Historical,'region'=>'CALABARZON','province'=>'Cavite','lat'=>14.3800,'lng'=>120.5700,'xp'=>80,'desc'=>'WWII fortress island in Manila Bay.'],
            ['name'=>'Fort Santiago','slug'=>'fort-santiago','cat'=>PlaceCategory::Historical,'region'=>'NCR','province'=>'Manila','lat'=>14.5950,'lng'=>120.9730,'xp'=>40,'desc'=>'Rizal\'s prison and Spanish citadel.'],
            ['name'=>'Banaue Rice Terraces','slug'=>'banaue-rice-terraces','cat'=>PlaceCategory::Historical,'region'=>'Cordillera','province'=>'Ifugao','lat'=>16.9200,'lng'=>121.0600,'xp'=>100,'desc'=>'2,000-year-old rice terraces carved into mountains.'],
            ['name'=>'Chocolate Hills','slug'=>'chocolate-hills','cat'=>PlaceCategory::Historical,'region'=>'Central Visayas','province'=>'Bohol','lat'=>9.7950,'lng'=>124.1670,'xp'=>60,'desc'=>'1,268 cone-shaped hills that turn brown in summer.'],
            ['name'=>'San Agustin Church','slug'=>'san-agustin-church','cat'=>PlaceCategory::Historical,'region'=>'NCR','province'=>'Manila','lat'=>14.5880,'lng'=>120.9750,'xp'=>35,'desc'=>'Oldest stone church in the Philippines (1607).'],
            ['name'=>'Magellan\'s Cross','slug'=>'magellans-cross','cat'=>PlaceCategory::Historical,'region'=>'Central Visayas','province'=>'Cebu','lat'=>10.2930,'lng'=>123.9050,'xp'=>45,'desc'=>'Planted by Ferdinand Magellan in 1521.'],

            // ── Food Destinations (8) ──
            ['name'=>'Binondo Food Walk','slug'=>'binondo-food-walk','cat'=>PlaceCategory::FoodDestination,'region'=>'NCR','province'=>'Manila','lat'=>14.6000,'lng'=>120.9740,'xp'=>40,'desc'=>'World\'s oldest Chinatown food crawl.'],
            ['name'=>'Pampanga Food Trail','slug'=>'pampanga-food-trail','cat'=>PlaceCategory::FoodDestination,'region'=>'Central Luzon','province'=>'Pampanga','lat'=>15.0400,'lng'=>120.7100,'xp'=>50,'desc'=>'Culinary capital of the Philippines.'],
            ['name'=>'Iloilo La Paz Batchoy','slug'=>'iloilo-batchoy','cat'=>PlaceCategory::FoodDestination,'region'=>'Western Visayas','province'=>'Iloilo','lat'=>10.7200,'lng'=>122.5600,'xp'=>35,'desc'=>'Home of the original La Paz Batchoy.'],
            ['name'=>'Cebu Lechon Trail','slug'=>'cebu-lechon-trail','cat'=>PlaceCategory::FoodDestination,'region'=>'Central Visayas','province'=>'Cebu','lat'=>10.3157,'lng'=>123.8854,'xp'=>45,'desc'=>'Best lechon in the Philippines.'],
            ['name'=>'Lucban Longganisa','slug'=>'lucban-longganisa','cat'=>PlaceCategory::FoodDestination,'region'=>'CALABARZON','province'=>'Quezon','lat'=>14.1100,'lng'=>121.5500,'xp'=>30,'desc'=>'Famous garlic longganisa and Pahiyas festival.'],
            ['name'=>'Davao Durian Market','slug'=>'davao-durian-market','cat'=>PlaceCategory::FoodDestination,'region'=>'Davao','province'=>'Davao del Sur','lat'=>7.0700,'lng'=>125.6100,'xp'=>40,'desc'=>'Durian capital of the Philippines.'],
            ['name'=>'Siargao Food Scene','slug'=>'siargao-food-scene','cat'=>PlaceCategory::FoodDestination,'region'=>'Caraga','province'=>'Surigao del Norte','lat'=>9.8500,'lng'=>126.0500,'xp'=>35,'desc'=>'Trendy cafes and fresh seafood.'],
            ['name'=>'Baguio Market','slug'=>'baguio-market','cat'=>PlaceCategory::FoodDestination,'region'=>'Cordillera','province'=>'Benguet','lat'=>16.4100,'lng'=>120.5900,'xp'=>30,'desc'=>'Night market and strawberry farms.'],

            // ── Road Trips (6) ──
            ['name'=>'Halsema Highway','slug'=>'halsema-highway','cat'=>PlaceCategory::RoadTrip,'region'=>'Cordillera','province'=>'Benguet','lat'=>16.7500,'lng'=>120.8200,'xp'=>80,'desc'=>'Highest highway in the Philippines.'],
            ['name'=>'Kennon Road','slug'=>'kennon-road','cat'=>PlaceCategory::RoadTrip,'region'=>'Cordillera','province'=>'Benguet','lat'=>16.3800,'lng'=>120.5500,'xp'=>50,'desc'=>'Scenic zigzag road to Baguio.'],
            ['name'=>'Pacific Coast Road','slug'=>'pacific-coast-road','cat'=>PlaceCategory::RoadTrip,'region'=>'CALABARZON','province'=>'Quezon','lat'=>14.0500,'lng'=>122.1000,'xp'=>70,'desc'=>'Coastal drive along the Pacific.'],
            ['name'=>'Sabtang Island Loop','slug'=>'sabtang-loop','cat'=>PlaceCategory::RoadTrip,'region'=>'Cagayan Valley','province'=>'Batanes','lat'=>20.3500,'lng'=>121.8800,'xp'=>90,'desc'=>'Scenic loop around Sabtang Island.'],
            ['name'=>'Bukidnon Highlands','slug'=>'bukidnon-highlands','cat'=>PlaceCategory::RoadTrip,'region'=>'Northern Mindanao','province'=>'Bukidnon','lat'=>8.0500,'lng'=>125.0000,'xp'=>65,'desc'=>'Pineapple fields and highland views.'],
            ['name'=>'Ilocos Norte Coastal','slug'=>'ilocos-norte-coastal','cat'=>PlaceCategory::RoadTrip,'region'=>'Ilocos','province'=>'Ilocos Norte','lat'=>18.2000,'lng'=>120.6000,'xp'=>60,'desc'=>'Windmills, sand dunes, and heritage towns.'],

            // ── Hidden Gems (10) ──
            ['name'=>'Sumaguing Cave','slug'=>'sumaguing-cave','cat'=>PlaceCategory::HiddenGem,'region'=>'Cordillera','province'=>'Mountain Province','lat'=>17.0800,'lng'=>121.0200,'xp'=>90,'desc'=>'Spelunking adventure in Sagada.'],
            ['name'=>'Hinatuan Secret Beach','slug'=>'hinatuan-secret-beach','cat'=>PlaceCategory::HiddenGem,'region'=>'Caraga','province'=>'Surigao del Sur','lat'=>8.3700,'lng'=>126.3400,'xp'=>100,'desc'=>'Hidden beach accessible only by boat.'],
            ['name'=>'Tojoman Lagoon','slug'=>'tojoman-lagoon','cat'=>PlaceCategory::HiddenGem,'region'=>'Caraga','province'=>'Surigao del Norte','lat'=>9.9200,'lng'=>126.0800,'xp'=>75,'desc'=>'Stingless jellyfish sanctuary.'],
            ['name'=>'Biri Rock Formations','slug'=>'biri-rock-formations','cat'=>PlaceCategory::HiddenGem,'region'=>'Eastern Visayas','province'=>'Northern Samar','lat'=>12.6700,'lng'=>124.6300,'xp'=>85,'desc'=>'Dramatic rock formations shaped by waves.'],
            ['name'=>'Mapawa Nature Park','slug'=>'mapawa-nature-park','cat'=>PlaceCategory::HiddenGem,'region'=>'Northern Mindanao','province'=>'Misamis Oriental','lat'=>8.4800,'lng'=>124.6500,'xp'=>65,'desc'=>'Waterfall rappelling and river trekking.'],
            ['name'=>'Sambawan Island','slug'=>'sambawan-island','cat'=>PlaceCategory::HiddenGem,'region'=>'Eastern Visayas','province'=>'Biliran','lat'=>11.5800,'lng'=>124.4200,'xp'=>80,'desc'=>'Uninhabited island with panoramic views.'],
            ['name'=>'Tinuy-an Falls','slug'=>'tinuy-an-falls','cat'=>PlaceCategory::HiddenGem,'region'=>'Caraga','province'=>'Surigao del Sur','lat'=>8.5500,'lng'=>126.1500,'xp'=>70,'desc'=>'Mini Niagara of the Philippines.'],
            ['name'=>'Kaparkan Falls','slug'=>'kaparkan-falls','cat'=>PlaceCategory::HiddenGem,'region'=>'Cordillera','province'=>'Abra','lat'=>17.5500,'lng'=>120.7500,'xp'=>110,'desc'=>'Terraced waterfall in remote Abra.'],
            ['name'=>'Sohoton Cove','slug'=>'sohoton-cove','cat'=>PlaceCategory::HiddenGem,'region'=>'Eastern Visayas','province'=>'Samar','lat'=>11.8200,'lng'=>124.9800,'xp'=>95,'desc'=>'Natural bridge and stingless jellyfish.'],
            ['name'=>'Danjugan Island','slug'=>'danjugan-island','cat'=>PlaceCategory::HiddenGem,'region'=>'Western Visayas','province'=>'Negros Occidental','lat'=>9.0800,'lng'=>122.3500,'xp'=>85,'desc'=>'Marine sanctuary with five lagoons.'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function seedEvents(array $organizers, $places): \Illuminate\Support\Collection
    {
        $events = collect();
        $ruleTypes = ['requirement','inclusion','exclusion','reminder','policy','instruction','what_to_bring'];

        $eventData = [
            ['title'=>'Mt. Pulag Sea of Clouds','slug'=>'mt-pulag-soc-'.Str::random(4),'org'=>0,'status'=>EventStatus::Published,'date'=>15,'fee'=>3500,'slots'=>20,'diff'=>'hard','meet'=>'Baguio City, Session Road','meet_time'=>'11:00 PM','places'=>['mt-pulag','mt-pulag-campsite'],'desc'=>'Witness the famous sea of clouds at 2,922 masl. 2D1N camping adventure.'],
            ['title'=>'Boracay Island Hopping','slug'=>'boracay-hop-'.Str::random(4),'org'=>1,'status'=>EventStatus::Published,'date'=>10,'fee'=>2500,'slots'=>25,'diff'=>'easy','meet'=>'Caticlan Jetty Port','meet_time'=>'7:00 AM','places'=>['boracay-white-beach','puka-shell-beach'],'desc'=>'Full day island hopping around Boracay.'],
            ['title'=>'El Nido Tour A + C','slug'=>'elnido-ac-'.Str::random(4),'org'=>1,'status'=>EventStatus::Published,'date'=>20,'fee'=>4500,'slots'=>15,'diff'=>'easy','meet'=>'El Nido Town Proper','meet_time'=>'8:00 AM','places'=>['el-nido','nacpan-beach'],'desc'=>'Lagoons, beaches, and snorkeling in El Nido.'],
            ['title'=>'Kawasan Canyoneering','slug'=>'kawasan-canyon-'.Str::random(4),'org'=>2,'status'=>EventStatus::Completed,'date'=>-7,'fee'=>2800,'slots'=>20,'diff'=>'moderate','meet'=>'Cebu South Bus Terminal','meet_time'=>'4:00 AM','places'=>['kawasan-falls','tumalog-falls'],'desc'=>'Canyoneering from Badian to Kawasan Falls.'],
            ['title'=>'Siargao Surf Camp 3D2N','slug'=>'siargao-surf-'.Str::random(4),'org'=>1,'status'=>EventStatus::Published,'date'=>25,'fee'=>6000,'slots'=>12,'diff'=>'moderate','meet'=>'Siargao Airport','meet_time'=>'10:00 AM','places'=>['siargao-island','daku-beach'],'desc'=>'Learn to surf at Cloud 9 + island hopping.'],
            ['title'=>'Batanes Heritage Tour','slug'=>'batanes-tour-'.Str::random(4),'org'=>3,'status'=>EventStatus::Published,'date'=>30,'fee'=>8000,'slots'=>10,'diff'=>'easy','meet'=>'Basco Airport','meet_time'=>'9:00 AM','places'=>['batanes','sabtang-loop'],'desc'=>'3D2N cultural and scenic tour of Batanes.'],
            ['title'=>'Sagada Spelunking','slug'=>'sagada-cave-'.Str::random(4),'org'=>2,'status'=>EventStatus::Draft,'date'=>35,'fee'=>3000,'slots'=>15,'diff'=>'hard','meet'=>'Baguio City','meet_time'=>'10:00 PM','places'=>['sumaguing-cave','banaue-rice-terraces'],'desc'=>'Cave connection + hanging coffins + rice terraces.'],
            ['title'=>'Coron Wreck Diving','slug'=>'coron-dive-'.Str::random(4),'org'=>1,'status'=>EventStatus::PendingReview,'date'=>22,'fee'=>7500,'slots'=>8,'diff'=>'hard','meet'=>'Coron Town','meet_time'=>'6:00 AM','places'=>['coron','kayangan-lake'],'desc'=>'Explore WWII Japanese shipwrecks + Kayangan Lake.'],
            ['title'=>'Mt. Apo Summit Climb','slug'=>'mt-apo-climb-'.Str::random(4),'org'=>0,'status'=>EventStatus::Published,'date'=>40,'fee'=>5500,'slots'=>15,'diff'=>'extreme','meet'=>'Davao City','meet_time'=>'5:00 AM','places'=>['mt-apo','camp-sabros'],'desc'=>'3D2N climb to the highest peak in the Philippines.'],
            ['title'=>'Enchanted River Day Trip','slug'=>'enchanted-river-'.Str::random(4),'org'=>2,'status'=>EventStatus::Completed,'date'=>-14,'fee'=>1500,'slots'=>30,'diff'=>'easy','meet'=>'Butuan City','meet_time'=>'5:00 AM','places'=>['enchanted-river','tinuy-an-falls'],'desc'=>'Visit the mystical blue river + Tinuy-an Falls.'],
            ['title'=>'Vigan Heritage Walk','slug'=>'vigan-walk-'.Str::random(4),'org'=>3,'status'=>EventStatus::Published,'date'=>18,'fee'=>1200,'slots'=>25,'diff'=>'easy','meet'=>'Vigan Plaza','meet_time'=>'8:00 AM','places'=>['vigan-heritage','hundred-islands'],'desc'=>'Walk the cobblestone streets + Hundred Islands side trip.'],
            ['title'=>'Chico River Rafting','slug'=>'chico-raft-'.Str::random(4),'org'=>2,'status'=>EventStatus::Cancelled,'date'=>-21,'fee'=>2000,'slots'=>12,'diff'=>'hard','meet'=>'Tabuk City','meet_time'=>'6:00 AM','places'=>['chico-river'],'desc'=>'Whitewater rafting adventure on the Chico River.'],
            ['title'=>'Binondo Food Crawl','slug'=>'binondo-food-'.Str::random(4),'org'=>3,'status'=>EventStatus::Published,'date'=>7,'fee'=>800,'slots'=>20,'diff'=>'easy','meet'=>'Binondo Church','meet_time'=>'10:00 AM','places'=>['binondo-food-walk','intramuros'],'desc'=>'Eat your way through the world\'s oldest Chinatown.'],
            ['title'=>'Lake Holon Trek','slug'=>'lake-holon-'.Str::random(4),'org'=>0,'status'=>EventStatus::Published,'date'=>28,'fee'=>3200,'slots'=>15,'diff'=>'moderate','meet'=>'T\'boli Town','meet_time'=>'4:00 AM','places'=>['lake-holon','lake-sebu'],'desc'=>'Trek to the crater lake + T\'boli cultural immersion.'],
            ['title'=>'Masungi Discovery','slug'=>'masungi-disc-'.Str::random(4),'org'=>0,'status'=>EventStatus::Full,'date'=>5,'fee'=>1800,'slots'=>10,'diff'=>'moderate','meet'=>'Masungi Georeserve Gate','meet_time'=>'7:00 AM','places'=>['masungi-georeserve','mt-daraitan'],'desc'=>'Rope courses, hammocks, and conservation trail.'],
        ];

        foreach ($eventData as $ed) {
            $firstPlace = $places->firstWhere('slug', $ed['places'][0]);
            $event = Event::create([
                'organizer_id' => $organizers[$ed['org']]->id,
                'place_id' => $firstPlace?->id,
                'title' => $ed['title'],
                'slug' => $ed['slug'],
                'description' => $ed['desc'],
                'category' => $firstPlace?->category,
                'event_date' => now()->addDays($ed['date']),
                'meeting_place' => $ed['meet'],
                'meeting_time' => $ed['meet_time'],
                'fee' => $ed['fee'],
                'max_slots' => $ed['slots'],
                'difficulty' => $ed['diff'],
                'status' => $ed['status'],
                'auto_approve_bookings' => $ed['fee'] < 2000,
            ]);

            // Itinerary
            foreach ($ed['places'] as $si => $placeSlug) {
                $p = $places->firstWhere('slug', $placeSlug);
                if ($p) {
                    EventPlace::create([
                        'event_id' => $event->id,
                        'place_id' => $p->id,
                        'day_number' => $si < 2 ? 1 : 2,
                        'sort_order' => $si + 1,
                        'activity' => collect(['Trekking','Swimming','Sightseeing','Camping','Diving','Eating','Exploring'])->random(),
                        'time_slot' => collect(['6:00 AM - 12:00 PM','1:00 PM - 5:00 PM','8:00 AM - 3:00 PM','5:00 AM - 10:00 AM'])->random(),
                    ]);
                }
            }

            // Rules (2-4 per event)
            $ruleCount = rand(2, 4);
            $ruleContents = [
                'requirement' => ['Bring valid government ID','Must be 18 years old and above','Medical certificate required','Physical fitness required'],
                'inclusion' => ['Meals included','Guide fee included','Boat transfer included','Camping gear provided'],
                'exclusion' => ['Personal expenses not included','Travel insurance not included','Transportation to meeting place not included'],
                'reminder' => ['Wear comfortable hiking shoes','Bring extra clothes','Charge your phone fully','Bring cash for tips'],
                'what_to_bring' => ['Headlamp or flashlight','1.5L water minimum','Trail food and snacks','Rain jacket or poncho','Sunscreen and insect repellent'],
                'policy' => ['No littering — pack in, pack out','Follow the guide at all times','No alcohol during the trek'],
                'instruction' => ['Meet at the exact time — we will not wait','Wear layers for cold weather','Register at the barangay hall'],
            ];

            for ($r = 0; $r < $ruleCount; $r++) {
                $type = $ruleTypes[array_rand($ruleTypes)];
                EventRule::create([
                    'event_id' => $event->id,
                    'rule_type' => $type,
                    'content' => $ruleContents[$type][array_rand($ruleContents[$type])],
                    'sort_order' => $r + 1,
                ]);
            }

            $events->push($event);
        }

        return $events;
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function seedBookings($events, $users): void
    {
        foreach ($events as $event) {
            if (in_array($event->status, [EventStatus::Draft, EventStatus::Cancelled])) continue;

            $joinerCount = match ($event->status) {
                EventStatus::Full => $event->max_slots,
                EventStatus::Completed => rand(5, min(15, $event->max_slots)),
                default => rand(3, min(10, $event->max_slots)),
            };

            $joiners = $users->random(min($joinerCount, $users->count()));

            foreach ($joiners as $user) {
                $status = match ($event->status) {
                    EventStatus::Completed => BookingStatus::Approved,
                    EventStatus::Full => BookingStatus::Approved,
                    default => collect([BookingStatus::Approved, BookingStatus::Approved, BookingStatus::Pending])->random(),
                };

                Booking::firstOrCreate([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                ], [
                    'status' => $status,
                    'approved_at' => $status === BookingStatus::Approved ? now()->subDays(rand(1, 14)) : null,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function seedUnlocks($users, $places, XpService $xpService): void
    {
        // Completed events → unlock places for approved attendees
        $completedEvents = Event::where('status', EventStatus::Completed)->with(['bookings', 'itinerary'])->get();

        foreach ($completedEvents as $event) {
            $approvedBookings = $event->bookings->where('status', BookingStatus::Approved);
            $itineraryPlaceIds = $event->itinerary->pluck('place_id')->filter();

            foreach ($approvedBookings as $booking) {
                foreach ($itineraryPlaceIds as $placeId) {
                    $place = $places->firstWhere('id', $placeId);
                    if (!$place) continue;

                    $exists = PlaceUnlock::where('user_id', $booking->user_id)->where('place_id', $placeId)->exists();
                    if ($exists) continue;

                    PlaceUnlock::create([
                        'user_id' => $booking->user_id,
                        'place_id' => $placeId,
                        'unlock_method' => UnlockMethod::EventCompletion,
                        'event_id' => $event->id,
                    ]);

                    // Award XP
                    $user = $users->firstWhere('id', $booking->user_id);
                    if ($user && $place->xp_reward > 0) {
                        $xpService->awardXp($user, $place->xp_reward);
                        $user->refresh();
                    }
                }
            }
        }

        // Random extra unlocks for variety (simulate self-reports)
        $randomUsers = $users->random(min(15, $users->count()));
        foreach ($randomUsers as $user) {
            $randomPlaces = $places->random(rand(1, 5));
            foreach ($randomPlaces as $place) {
                $exists = PlaceUnlock::where('user_id', $user->id)->where('place_id', $place->id)->exists();
                if ($exists) continue;

                PlaceUnlock::create([
                    'user_id' => $user->id,
                    'place_id' => $place->id,
                    'unlock_method' => UnlockMethod::SelfReport,
                ]);

                if ($place->xp_reward > 0) {
                    $xpService->awardXp($user, $place->xp_reward);
                    $user->refresh();
                }
            }
        }
    }
}
