<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LakbayXP') }} — Discover. Explore. Conquer.</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif; }
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #0891b2 50%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-gradient { background: linear-gradient(180deg, #ecfdf5 0%, #f0fdfa 30%, #ffffff 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.1); }
        .category-pill { transition: all 0.2s ease; }
        .category-pill:hover { transform: scale(1.05); }
        .float-animation { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
        .blob { border-radius: 30% 70% 70% 30%/30% 30% 70% 70%; animation: blob 8s ease-in-out infinite; }

        /* Marquee infinite scroll */
        .marquee-track { overflow: hidden; width: 100%; }
        .marquee-scroll { display: flex; gap: 1.25rem; width: max-content; animation: marquee-left 40s linear infinite; }
        .marquee-reverse { animation: marquee-right 45s linear infinite; }
        .marquee-track:hover .marquee-scroll { animation-play-state: paused; }
        @keyframes marquee-left { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        @keyframes marquee-right { 0% { transform: translateX(-50%); } 100% { transform: translateX(0); } }

        /* Map marker animations */
        .map-ping { animation: map-ping 3s ease-in-out infinite; }
        .map-marker { animation: map-glow 2s ease-in-out infinite; cursor: pointer; }
        .map-float { animation: float 6s ease-in-out infinite; }
        @keyframes map-ping { 0%,100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 0.8; transform: scale(1.5); } }
        @keyframes map-glow { 0%,100% { opacity: 0.85; } 50% { opacity: 1; } }

        /* Leaflet popup override */
        .map-popup .leaflet-popup-content-wrapper { background: rgba(15,23,42,0.92); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        .map-popup .leaflet-popup-tip { background: rgba(15,23,42,0.92); }
        .map-popup .leaflet-popup-close-button { color: rgba(255,255,255,0.5); }
        .map-popup .leaflet-popup-close-button:hover { color: #fff; }
        #ph-map { background: transparent; position: relative; z-index: 1; }
        #ph-map .leaflet-control-attribution { display: none; }
        .leaflet-pane { z-index: 1 !important; }
        .leaflet-top, .leaflet-bottom { z-index: 2 !important; }
        @keyframes blob { 0%,100%{border-radius:30% 70% 70% 30%/30% 30% 70% 70%} 50%{border-radius:70% 30% 30% 70%/70% 70% 30% 30%} }
        .xp-bar { background: linear-gradient(90deg, #059669, #0891b2, #6366f1); }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">Lakbay<span class="gradient-text">XP</span></span>
                </a>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#events" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Events</a>
                    <a href="#places" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Places</a>
                    <a href="#xp" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">XP & Levels</a>
                    <a href="{{ route('rewards.page') }}" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Rewards</a>
                    <a href="{{ route('leaderboard') }}" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Leaderboard</a>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-full hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-md shadow-emerald-500/20">
                            Organizer Portal
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient pt-32 pb-20 overflow-hidden relative">
        <div class="absolute top-20 -left-20 w-72 h-72 bg-emerald-200/30 blob"></div>
        <div class="absolute top-40 -right-20 w-96 h-96 bg-cyan-200/20 blob" style="animation-delay:-4s"></div>
        <div class="absolute bottom-0 left-1/3 w-64 h-64 bg-indigo-200/20 blob" style="animation-delay:-2s"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    The Philippines' Travel Adventure Community
                </div>
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] mb-6">
                    Discover. Explore.<br>
                    <span class="gradient-text">Level Up Your Adventure.</span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Join organized adventures, earn XP and badges, level up from 1 to 100,
                    collect points, and redeem exclusive rewards. Your travel journey starts here.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#download" class="inline-flex items-center gap-2 px-8 py-3.5 text-base font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-full hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/30">
                        Download the App
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    <a href="#events" class="inline-flex items-center gap-2 px-8 py-3.5 text-base font-semibold text-gray-700 bg-white border border-gray-200 rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all">
                        Browse Events
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                @php
                    $totalPlaces = \App\Models\Place::where('is_active', true)->count();
                    $totalEvents = \App\Models\Event::whereIn('status', ['published', 'full', 'completed'])->count();
                    $totalUsers = \App\Models\User::where('role', 'user')->count();
                    $totalBadges = \App\Models\Badge::where('is_active', true)->count();
                @endphp
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl font-extrabold gradient-text">{{ $totalPlaces }}+</div>
                    <div class="text-sm text-gray-500 mt-1">Destinations</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl font-extrabold gradient-text">{{ $totalEvents }}+</div>
                    <div class="text-sm text-gray-500 mt-1">Adventures</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl font-extrabold gradient-text">{{ $totalUsers }}+</div>
                    <div class="text-sm text-gray-500 mt-1">Explorers</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl sm:text-4xl font-extrabold gradient-text">{{ $totalBadges }}</div>
                    <div class="text-sm text-gray-500 mt-1">Badges to Earn</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="py-12 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="{{ route('leaderboard') }}" class="group card-hover flex flex-col items-center gap-3 p-6 rounded-2xl bg-gradient-to-b from-emerald-50 to-white border border-emerald-100 hover:border-emerald-300 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-2xl shadow-lg shadow-emerald-200/50 group-hover:scale-110 transition-transform">🏆</div>
                    <span class="text-sm font-bold text-gray-900">Leaderboard</span>
                    <span class="text-[10px] text-gray-400">Top explorers</span>
                </a>
                <a href="{{ route('rewards.page') }}" class="group card-hover flex flex-col items-center gap-3 p-6 rounded-2xl bg-gradient-to-b from-amber-50 to-white border border-amber-100 hover:border-amber-300 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-400 flex items-center justify-center text-2xl shadow-lg shadow-amber-200/50 group-hover:scale-110 transition-transform">🎁</div>
                    <span class="text-sm font-bold text-gray-900">Rewards</span>
                    <span class="text-[10px] text-gray-400">Redeem points</span>
                </a>
                <a href="#places" class="group card-hover flex flex-col items-center gap-3 p-6 rounded-2xl bg-gradient-to-b from-cyan-50 to-white border border-cyan-100 hover:border-cyan-300 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-400 flex items-center justify-center text-2xl shadow-lg shadow-cyan-200/50 group-hover:scale-110 transition-transform">🗺️</div>
                    <span class="text-sm font-bold text-gray-900">Explore Places</span>
                    <span class="text-[10px] text-gray-400">{{ \App\Models\Place::where('is_active', true)->count() }}+ destinations</span>
                </a>
                <a href="#download" class="group card-hover flex flex-col items-center gap-3 p-6 rounded-2xl bg-gradient-to-b from-indigo-50 to-white border border-indigo-100 hover:border-indigo-300 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center text-2xl shadow-lg shadow-indigo-200/50 group-hover:scale-110 transition-transform">👥</div>
                    <span class="text-sm font-bold text-gray-900">Explorers</span>
                    <span class="text-[10px] text-gray-400">{{ \App\Models\User::count() }}+ adventurers</span>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-28 overflow-hidden relative" style="background: linear-gradient(180deg, #f0fdf4 0%, #ecfeff 30%, #eef2ff 60%, #fdf2f8 85%, #fff 100%);">
        {{-- Floating background icons --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-16 left-[8%] text-7xl opacity-[0.07] float-animation" style="animation-delay:-1s">🔍</div>
            <div class="absolute top-[20%] right-[5%] text-8xl opacity-[0.06] float-animation" style="animation-delay:-3s">🎫</div>
            <div class="absolute top-[40%] left-[3%] text-9xl opacity-[0.05] float-animation" style="animation-delay:-5s">🏔️</div>
            <div class="absolute top-[55%] right-[8%] text-7xl opacity-[0.07] float-animation" style="animation-delay:-2s">⚡</div>
            <div class="absolute top-[75%] left-[6%] text-8xl opacity-[0.06] float-animation" style="animation-delay:-4s">🎁</div>
            <div class="absolute bottom-20 right-[12%] text-6xl opacity-[0.05] float-animation" style="animation-delay:-6s">🚀</div>
            {{-- Blurred blobs --}}
            <div class="absolute top-32 -left-20 w-72 h-72 bg-emerald-200/30 rounded-full blur-3xl"></div>
            <div class="absolute top-[45%] -right-20 w-80 h-80 bg-cyan-200/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-32 left-1/4 w-64 h-64 bg-indigo-200/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-1/4 w-56 h-56 bg-rose-200/20 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-20">
                <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/80 backdrop-blur text-emerald-700 text-sm font-bold uppercase tracking-wider mb-5 shadow-sm border border-emerald-100">Your Adventure Path</span>
                <h2 class="text-4xl sm:text-6xl font-extrabold tracking-tight mb-5">How <span class="gradient-text">LakbayXP</span> Works</h2>
                <p class="text-gray-500 text-xl max-w-xl mx-auto leading-relaxed">From curious explorer to legendary adventurer — here's your journey from Level 1 to Level 100.</p>
            </div>

            <!-- Vertical Timeline -->
            <div class="relative">
                <!-- Timeline Line (thicker, glowing) -->
                <div class="absolute left-7 sm:left-1/2 sm:-translate-x-px top-0 bottom-0 w-1 rounded-full bg-gradient-to-b from-emerald-400 via-cyan-400 via-indigo-400 via-purple-400 to-rose-400 shadow-lg shadow-emerald-500/20"></div>

                @php
                    $steps = [
                        [
                            'icon' => '🔍',
                            'title' => 'Discover Adventures',
                            'desc' => 'Browse curated events organized by verified local guides and travel agencies. From mountain summits to hidden beaches — find the perfect adventure that matches your vibe.',
                            'detail' => 'Filter by category, difficulty, date, and location',
                            'color' => 'emerald',
                            'number' => '01',
                        ],
                        [
                            'icon' => '🎫',
                            'title' => 'Book Your Slot',
                            'desc' => 'Reserve your spot with just a tap. Each event has limited slots to keep groups small and the experience personal. Get instant confirmation from organizers.',
                            'detail' => 'Auto-approve or manual review by organizer',
                            'color' => 'cyan',
                            'number' => '02',
                        ],
                        [
                            'icon' => '🏔️',
                            'title' => 'Conquer the Trail',
                            'desc' => 'Show up, explore, and complete the adventure. Follow the itinerary, meet fellow explorers, and experience the Philippines like never before.',
                            'detail' => 'Multi-day events with detailed itineraries',
                            'color' => 'indigo',
                            'number' => '03',
                        ],
                        [
                            'icon' => '⚡',
                            'title' => 'Earn XP, Badges & Unlock Places',
                            'desc' => 'Every place you conquer gets unlocked on your profile and earns you XP to level up. Collect badges as trophies of your adventures — each badge also gives you redeemable points for freebies.',
                            'detail' => 'XP for leveling, badges for bragging rights + points',
                            'color' => 'purple',
                            'number' => '04',
                        ],
                        [
                            'icon' => '🎁',
                            'title' => 'Redeem Rewards',
                            'desc' => 'Trade your badge points for exclusive freebies — discounted trips, merch, partner deals, and more. The more you explore, the more you earn.',
                            'detail' => 'New rewards added regularly by admin',
                            'color' => 'rose',
                            'number' => '05',
                        ],
                    ];
                @endphp

                <div class="space-y-14 sm:space-y-20">
                    @foreach($steps as $i => $step)
                        @php $isLeft = $i % 2 === 0; @endphp
                        <div class="relative flex items-start gap-6 sm:gap-0 group">
                            {{-- Timeline Dot --}}
                            <div class="absolute left-4 sm:left-1/2 sm:-translate-x-1/2 z-10 w-8 h-8 rounded-full bg-{{ $step['color'] }}-500 ring-[5px] ring-white shadow-xl shadow-{{ $step['color'] }}-500/40 group-hover:scale-125 transition-transform duration-300 flex items-center justify-center">
                                <div class="w-3 h-3 rounded-full bg-white/80"></div>
                            </div>

                            {{-- Content --}}
                            <div class="sm:w-[58%] {{ $isLeft ? 'sm:pr-14 ml-16 sm:ml-0' : 'sm:pl-14 sm:ml-auto ml-16' }}">
                                @php
                                    $tints = ['emerald'=>'#ecfdf5','cyan'=>'#ecfeff','indigo'=>'#eef2ff','purple'=>'#faf5ff','rose'=>'#fff1f2'];
                                @endphp
                                <div class="relative rounded-3xl p-8 sm:p-9 shadow-md hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-{{ $step['color'] }}-100/60 overflow-hidden" style="background: linear-gradient(135deg, white 60%, {{ $tints[$step['color']] ?? '#f9fafb' }});">
                                    {{-- Decorative background icon --}}
                                    <div class="absolute -bottom-4 -right-4 text-8xl opacity-[0.06] pointer-events-none select-none">{{ $step['icon'] }}</div>
                                    {{-- Colored accent bar --}}
                                    <div class="absolute top-0 {{ $isLeft ? 'right-0' : 'left-0' }} w-1 h-full bg-gradient-to-b from-{{ $step['color'] }}-400 to-{{ $step['color'] }}-600 rounded-full"></div>

                                    {{-- Arrow pointer (desktop only) --}}
                                    <div class="hidden sm:block absolute top-9 {{ $isLeft ? '-right-2.5' : '-left-2.5' }} w-5 h-5 bg-white rotate-45 border-{{ $step['color'] }}-100/60 {{ $isLeft ? 'border-r border-t' : 'border-l border-b' }}"></div>

                                    {{-- Step Number + Icon --}}
                                    <div class="flex items-center gap-4 mb-5 relative">
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-{{ $step['color'] }}-50 to-{{ $step['color'] }}-100/50 border border-{{ $step['color'] }}-200/50 flex items-center justify-center text-3xl shrink-0 float-animation shadow-lg shadow-{{ $step['color'] }}-500/10" style="animation-delay: {{ $i * -1.5 }}s">
                                            {{ $step['icon'] }}
                                        </div>
                                        <div>
                                            <span class="text-xs font-extrabold text-{{ $step['color'] }}-400 uppercase tracking-widest">Step {{ $step['number'] }}</span>
                                            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight leading-tight">{{ $step['title'] }}</h3>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <p class="text-base text-gray-500 leading-relaxed mb-5">{{ $step['desc'] }}</p>

                                    {{-- Detail tag --}}
                                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-{{ $step['color'] }}-50 text-{{ $step['color'] }}-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="text-sm font-semibold">{{ $step['detail'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Final CTA at bottom of timeline --}}
                <div class="relative mt-20 flex justify-center">
                    <div class="absolute left-7 sm:left-1/2 sm:-translate-x-1/2 -top-10 w-0.5 h-10 bg-gradient-to-b from-rose-400 to-transparent"></div>
                    <div class="relative z-10 text-center">
                        <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center text-4xl shadow-xl shadow-emerald-500/20 float-animation" style="background: linear-gradient(135deg, #059669, #0891b2);">
                            🚀
                        </div>
                        <h3 class="text-2xl font-extrabold text-gray-900 mb-3">Ready to start your journey?</h3>
                        <p class="text-base text-gray-400 mb-6">Download the app and begin your adventure today.</p>
                        <a href="#download" class="inline-flex items-center gap-2 px-10 py-4 text-base font-bold text-white rounded-full shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300" style="background: linear-gradient(135deg, #059669, #0891b2);">
                            Get Started
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Place Categories -->
    <section id="places" class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">Explore by Category</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">From towering peaks to hidden beaches — find your next adventure.</p>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                @php
                    $categoryIcons = [
                        'mountain' => '⛰️', 'beach' => '🏖️', 'island' => '🏝️', 'falls' => '💧',
                        'river' => '🌊', 'lake' => '🏞️', 'campsite' => '⛺', 'historical' => '🏛️',
                        'food_destination' => '🍜', 'road_trip' => '🚗', 'hidden_gem' => '💎',
                    ];
                    $categoryLabels = [
                        'mountain' => 'Mountains', 'beach' => 'Beaches', 'island' => 'Islands', 'falls' => 'Waterfalls',
                        'river' => 'Rivers', 'lake' => 'Lakes', 'campsite' => 'Campsites', 'historical' => 'Historical',
                        'food_destination' => 'Food Spots', 'road_trip' => 'Road Trips', 'hidden_gem' => 'Hidden Gems',
                    ];
                @endphp
                @foreach($categoryIcons as $key => $icon)
                    <div class="category-pill inline-flex items-center gap-2 px-5 py-3 rounded-full bg-white border border-gray-100 hover:bg-emerald-50 hover:border-emerald-200 cursor-pointer shadow-sm">
                        <span class="text-xl">{{ $icon }}</span>
                        <span class="text-sm font-semibold text-gray-700">{{ $categoryLabels[$key] }}</span>
                        <span class="text-xs text-gray-400">({{ \App\Models\Place::where('category', $key)->where('is_active', true)->count() }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Popular Destinations -->
    <section class="py-20 bg-gray-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-2">Popular Destinations</h2>
                <p class="text-gray-500">Places our community loves the most.</p>
            </div>
        </div>

        @php
            $scrollPlaces = \App\Models\Place::where('is_active', true)
                ->withCount('unlockedByUsers')
                ->inRandomOrder()
                ->take(24)
                ->get();
        @endphp

        <!-- Infinite scroll marquee -->
        <div class="relative">
            <!-- Fade edges -->
            <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-gray-50 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-gray-50 to-transparent z-10 pointer-events-none"></div>

            <!-- Row 1 - scrolls left -->
            <div class="marquee-track mb-5">
                <div class="marquee-scroll">
                    @foreach($scrollPlaces->take(12) as $place)
                        <div class="marquee-card shrink-0 w-72 rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="h-36 bg-gradient-to-br from-emerald-100 to-cyan-50 flex items-center justify-center relative overflow-hidden">
                                <span class="text-5xl opacity-50 group-hover:opacity-80 group-hover:scale-110 transition-all duration-300">{{ $categoryIcons[$place->category->value] ?? '📍' }}</span>
                                <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur text-[10px] font-bold text-emerald-700 border border-emerald-100 uppercase tracking-wider">
                                    {{ $categoryLabels[$place->category->value] ?? $place->category->value }}
                                </div>
                                @if($place->experience_points)
                                    <div class="absolute bottom-3 left-3 px-2 py-0.5 rounded-lg bg-white/90 backdrop-blur text-[10px] font-bold text-indigo-600">⚡ {{ $place->experience_points }} XP</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-base font-bold text-gray-900 mb-0.5 truncate group-hover:text-emerald-700 transition-colors">{{ $place->name }}</h3>
                                <p class="text-xs text-gray-400 mb-2">{{ $place->province ?? $place->region ?? 'Philippines' }}</p>
                                <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $place->unlocked_by_users_count }} conquered
                                </div>
                            </div>
                        </div>
                    @endforeach
                    {{-- Duplicate for seamless loop --}}
                    @foreach($scrollPlaces->take(12) as $place)
                        <div class="marquee-card shrink-0 w-72 rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="h-36 bg-gradient-to-br from-emerald-100 to-cyan-50 flex items-center justify-center relative overflow-hidden">
                                <span class="text-5xl opacity-50 group-hover:opacity-80 group-hover:scale-110 transition-all duration-300">{{ $categoryIcons[$place->category->value] ?? '📍' }}</span>
                                <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur text-[10px] font-bold text-emerald-700 border border-emerald-100 uppercase tracking-wider">
                                    {{ $categoryLabels[$place->category->value] ?? $place->category->value }}
                                </div>
                                @if($place->experience_points)
                                    <div class="absolute bottom-3 left-3 px-2 py-0.5 rounded-lg bg-white/90 backdrop-blur text-[10px] font-bold text-indigo-600">⚡ {{ $place->experience_points }} XP</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-base font-bold text-gray-900 mb-0.5 truncate group-hover:text-emerald-700 transition-colors">{{ $place->name }}</h3>
                                <p class="text-xs text-gray-400 mb-2">{{ $place->province ?? $place->region ?? 'Philippines' }}</p>
                                <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $place->unlocked_by_users_count }} conquered
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Row 2 - scrolls right (reverse) -->
            <div class="marquee-track">
                <div class="marquee-scroll marquee-reverse">
                    @foreach($scrollPlaces->skip(12) as $place)
                        <div class="marquee-card shrink-0 w-72 rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="h-36 bg-gradient-to-br from-indigo-100 to-purple-50 flex items-center justify-center relative overflow-hidden">
                                <span class="text-5xl opacity-50 group-hover:opacity-80 group-hover:scale-110 transition-all duration-300">{{ $categoryIcons[$place->category->value] ?? '📍' }}</span>
                                <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur text-[10px] font-bold text-indigo-700 border border-indigo-100 uppercase tracking-wider">
                                    {{ $categoryLabels[$place->category->value] ?? $place->category->value }}
                                </div>
                                @if($place->experience_points)
                                    <div class="absolute bottom-3 left-3 px-2 py-0.5 rounded-lg bg-white/90 backdrop-blur text-[10px] font-bold text-indigo-600">⚡ {{ $place->experience_points }} XP</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-base font-bold text-gray-900 mb-0.5 truncate group-hover:text-emerald-700 transition-colors">{{ $place->name }}</h3>
                                <p class="text-xs text-gray-400 mb-2">{{ $place->province ?? $place->region ?? 'Philippines' }}</p>
                                <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $place->unlocked_by_users_count }} conquered
                                </div>
                            </div>
                        </div>
                    @endforeach
                    {{-- Duplicate for seamless loop --}}
                    @foreach($scrollPlaces->skip(12) as $place)
                        <div class="marquee-card shrink-0 w-72 rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <div class="h-36 bg-gradient-to-br from-indigo-100 to-purple-50 flex items-center justify-center relative overflow-hidden">
                                <span class="text-5xl opacity-50 group-hover:opacity-80 group-hover:scale-110 transition-all duration-300">{{ $categoryIcons[$place->category->value] ?? '📍' }}</span>
                                <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur text-[10px] font-bold text-indigo-700 border border-indigo-100 uppercase tracking-wider">
                                    {{ $categoryLabels[$place->category->value] ?? $place->category->value }}
                                </div>
                                @if($place->experience_points)
                                    <div class="absolute bottom-3 left-3 px-2 py-0.5 rounded-lg bg-white/90 backdrop-blur text-[10px] font-bold text-indigo-600">⚡ {{ $place->experience_points }} XP</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-base font-bold text-gray-900 mb-0.5 truncate group-hover:text-emerald-700 transition-colors">{{ $place->name }}</h3>
                                <p class="text-xs text-gray-400 mb-2">{{ $place->province ?? $place->region ?? 'Philippines' }}</p>
                                <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $place->unlocked_by_users_count }} conquered
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Unlock Places -->
    <section class="py-24 bg-white relative overflow-hidden">
        {{-- Background decorations --}}
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 via-cyan-400 to-indigo-400"></div>
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-emerald-100/40 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-cyan-100/40 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold uppercase tracking-wider mb-4">🔓 Place Collection</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight mb-4">Unlock Places. <span class="gradient-text">Build Your Map.</span></h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">Every adventure you complete unlocks the places you've conquered. Watch your personal map grow as you explore the Philippines — one destination at a time.</p>
            </div>

            {{-- How Unlocking Works - 3 cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                @php
                    $unlockSteps = [
                        [
                            'icon' => '🎫',
                            'title' => 'Join an Event',
                            'desc' => 'Book a slot in any adventure organized by a verified guide. Show up and complete the trip.',
                            'color' => 'emerald',
                            'badge' => 'Step 1',
                        ],
                        [
                            'icon' => '🔓',
                            'title' => 'Place Unlocked',
                            'desc' => 'When the organizer marks the event as completed, every place in the itinerary gets automatically unlocked for you.',
                            'color' => 'cyan',
                            'badge' => 'Step 2',
                        ],
                        [
                            'icon' => '⚡',
                            'title' => 'Earn XP Instantly',
                            'desc' => 'Each unlocked place awards XP based on its difficulty and category. The harder the trail, the bigger the reward.',
                            'color' => 'indigo',
                            'badge' => 'Step 3',
                        ],
                    ];
                @endphp
                @foreach($unlockSteps as $i => $us)
                    <div class="relative group">
                        {{-- Connector line (between cards, desktop only) --}}
                        @if($i < 2)
                            <div class="hidden md:block absolute top-12 -right-3 w-6 h-0.5 bg-gradient-to-r from-{{ $us['color'] }}-300 to-{{ $unlockSteps[$i+1]['color'] }}-300 z-10"></div>
                        @endif
                        <div class="h-full bg-white rounded-2xl border border-gray-100 p-7 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group-hover:border-{{ $us['color'] }}-200">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-{{ $us['color'] }}-50 border border-{{ $us['color'] }}-100 flex items-center justify-center text-2xl shrink-0 float-animation" style="animation-delay: {{ $i * -2 }}s">
                                    {{ $us['icon'] }}
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full bg-{{ $us['color'] }}-50 text-{{ $us['color'] }}-700 text-[10px] font-extrabold uppercase tracking-widest">{{ $us['badge'] }}</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-gray-900 mb-2">{{ $us['title'] }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ $us['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Visual unlock showcase with PH Map --}}
            <div class="relative rounded-3xl overflow-hidden" style="background: linear-gradient(135deg, #059669 0%, #0891b2 50%, #6366f1 100%);">
                <div class="absolute inset-0 opacity-5">
                    <div class="absolute top-6 left-10 text-8xl">⛰️</div>
                    <div class="absolute bottom-6 right-10 text-8xl">🏝️</div>
                </div>

                <div class="relative p-8 sm:p-12">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                        {{-- Left: Stats & info --}}
                        <div class="text-white">
                            <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-4">Your Personal Adventure Map</h3>
                            <p class="text-white/70 text-sm leading-relaxed mb-8">Every place you unlock lights up on your map. Conquer mountains, beaches, and hidden gems across the Philippines — watch your map glow as you level up.</p>

                            <div class="grid grid-cols-3 gap-4 mb-8">
                                @php
                                    $totalPlaces = \App\Models\Place::where('is_active', true)->count();
                                    $totalUnlocks = \App\Models\PlaceUnlock::count();
                                    $categoryCount = \App\Models\Place::where('is_active', true)->distinct('category')->count('category');
                                @endphp
                                <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur">
                                    <div class="text-2xl sm:text-3xl font-extrabold">{{ $totalPlaces }}</div>
                                    <div class="text-[10px] font-semibold text-white/60 uppercase tracking-wider mt-1">Places</div>
                                </div>
                                <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur">
                                    <div class="text-2xl sm:text-3xl font-extrabold">{{ $totalUnlocks }}</div>
                                    <div class="text-[10px] font-semibold text-white/60 uppercase tracking-wider mt-1">Unlocks</div>
                                </div>
                                <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur">
                                    <div class="text-2xl sm:text-3xl font-extrabold">{{ $categoryCount }}</div>
                                    <div class="text-[10px] font-semibold text-white/60 uppercase tracking-wider mt-1">Categories</div>
                                </div>
                            </div>

                            {{-- Legend --}}
                            <div class="flex items-center gap-5 mb-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/50"></span>
                                    <span class="text-xs text-white/70 font-medium">Unlocked</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-white/20 border border-white/30"></span>
                                    <span class="text-xs text-white/70 font-medium">Locked</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-amber-400 animate-pulse"></span>
                                    <span class="text-xs text-white/70 font-medium">Popular</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex -space-x-2">
                                    @for($i = 0; $i < 4; $i++)
                                        <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center text-xs font-bold text-white">{{ ['🥾','🏔','🌊','⛺'][$i] }}</div>
                                    @endfor
                                </div>
                                <span class="text-xs text-white/60 font-medium">Join {{ \App\Models\User::where('role', 'user')->count() }}+ explorers</span>
                            </div>
                        </div>

                        {{-- Right: Philippines Map with Leaflet --}}
                        <div class="relative">
                            <div id="ph-map" class="w-full rounded-2xl overflow-hidden border border-white/10 shadow-2xl" style="height: 420px;"></div>

                            {{-- Floating tooltip cards --}}
                            @php
                                $mapPlaces = \App\Models\Place::where('is_active', true)
                                    ->whereNotNull('latitude')->whereNotNull('longitude')
                                    ->withCount('unlockedByUsers')
                                    ->get();
                                $topPlace = $mapPlaces->sortByDesc('unlocked_by_users_count')->first();
                                $secondPlace = $mapPlaces->sortByDesc('unlocked_by_users_count')->skip(1)->first();
                            @endphp
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const map = L.map('ph-map', {
                                    zoomControl: false,
                                    attributionControl: false,
                                    dragging: false,
                                    scrollWheelZoom: false,
                                    doubleClickZoom: false,
                                    touchZoom: false,
                                }).setView([12.0, 122.5], 5.5);

                                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                                    maxZoom: 19,
                                }).addTo(map);

                                @php
                                    $mapJson = $mapPlaces->map(function($p) {
                                        return [
                                            'name' => $p->name,
                                            'lat' => (float)$p->latitude,
                                            'lng' => (float)$p->longitude,
                                            'category' => $p->category->value,
                                            'xp' => $p->experience_points ?? 0,
                                            'unlocks' => $p->unlocked_by_users_count,
                                            'province' => $p->province ?? '',
                                        ];
                                    })->values();
                                @endphp
                                const places = @json($mapJson);

                                const maxUnlocks = Math.max(...places.map(p => p.unlocks), 1);

                                const categoryEmoji = {
                                    'mountain':'⛰️','beach':'🏖️','island':'🏝️','falls':'💧',
                                    'river':'🌊','lake':'🏞️','campsite':'⛺','historical':'🏛️',
                                    'food_destination':'🍜','road_trip':'🚗','hidden_gem':'💎'
                                };

                                places.forEach((p, i) => {
                                    const isPopular = p.unlocks >= maxUnlocks * 0.5;
                                    const size = isPopular ? 40 : 30;
                                    const color = isPopular ? '#fbbf24' : '#34d399';
                                    const emoji = categoryEmoji[p.category] || '📍';

                                    // Pulsing ring
                                    const pulseIcon = L.divIcon({
                                        className: '',
                                        html: `
                                            <div style="position:relative;width:${size}px;height:${size}px;">
                                                <div style="position:absolute;inset:0;border-radius:50%;border:2px solid ${color};opacity:0.4;animation:map-ping 3s ease-in-out infinite;animation-delay:${i*0.2}s"></div>
                                                <div style="position:absolute;inset:${(size-24)/2}px;width:24px;height:24px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;font-size:12px;box-shadow:0 0 12px ${color}80;cursor:pointer;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.3)'" onmouseout="this.style.transform='scale(1)'">${emoji}</div>
                                            </div>
                                        `,
                                        iconSize: [size, size],
                                        iconAnchor: [size/2, size/2],
                                    });

                                    L.marker([p.lat, p.lng], { icon: pulseIcon })
                                        .addTo(map)
                                        .bindPopup(`
                                            <div style="font-family:'Plus Jakarta Sans',sans-serif;min-width:140px;">
                                                <div style="font-size:13px;font-weight:800;margin-bottom:2px;">${emoji} ${p.name}</div>
                                                <div style="font-size:11px;color:#6b7280;">${p.province}</div>
                                                <div style="display:flex;gap:8px;margin-top:6px;font-size:11px;">
                                                    <span style="color:#059669;font-weight:700;">🔓 ${p.unlocks}</span>
                                                    <span style="color:#6366f1;font-weight:700;">⚡ ${p.xp} XP</span>
                                                </div>
                                            </div>
                                        `, { className: 'map-popup' });
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- XP & Leveling System -->
    <section id="xp" class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">XP & Leveling System</h2>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">Every badge you earn gives you XP. Accumulate XP to level up from Level 1 all the way to Level 100. The higher your level, the more rewards you can unlock and redeem.</p>
            </div>

            <!-- XP Demo -->
            <div class="max-w-lg mx-auto mb-12 p-6 rounded-2xl bg-white border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white font-extrabold text-lg">42</div>
                        <div>
                            <div class="font-bold text-gray-900">Level 42</div>
                            <div class="text-xs text-gray-400">Trail Hunter</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold gradient-text">12,450 XP</div>
                        <div class="text-xs text-gray-400">2,550 XP to Level 43</div>
                    </div>
                </div>
                <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="xp-bar h-full rounded-full" style="width: 83%"></div>
                </div>
            </div>

            <!-- Level Milestones -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 max-w-5xl mx-auto">
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-gray-100 shadow-sm">
                    <div class="text-2xl mb-2">🌱</div>
                    <div class="font-bold text-gray-900 text-sm">Lvl 1-5</div>
                    <div class="text-xs text-gray-400 mt-1">Newbie</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-gray-100 shadow-sm">
                    <div class="text-2xl mb-2">🚶</div>
                    <div class="font-bold text-gray-700 text-sm">Lvl 6-15</div>
                    <div class="text-xs text-gray-400 mt-1">Wanderer</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-emerald-100 shadow-sm">
                    <div class="text-2xl mb-2">🥾</div>
                    <div class="font-bold text-emerald-700 text-sm">Lvl 16-25</div>
                    <div class="text-xs text-gray-400 mt-1">Trekker</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-emerald-100 shadow-sm">
                    <div class="text-2xl mb-2">🧭</div>
                    <div class="font-bold text-emerald-700 text-sm">Lvl 26-35</div>
                    <div class="text-xs text-gray-400 mt-1">Pathfinder</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-cyan-100 shadow-sm">
                    <div class="text-2xl mb-2">🗺️</div>
                    <div class="font-bold text-cyan-700 text-sm">Lvl 36-45</div>
                    <div class="text-xs text-gray-400 mt-1">Trail Hunter</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-cyan-100 shadow-sm">
                    <div class="text-2xl mb-2">⛰️</div>
                    <div class="font-bold text-cyan-700 text-sm">Lvl 46-55</div>
                    <div class="text-xs text-gray-400 mt-1">Summit Seeker</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-indigo-100 shadow-sm">
                    <div class="text-2xl mb-2">🏔️</div>
                    <div class="font-bold text-indigo-700 text-sm">Lvl 56-70</div>
                    <div class="text-xs text-gray-400 mt-1">Peak Conqueror</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-indigo-100 shadow-sm">
                    <div class="text-2xl mb-2">🦅</div>
                    <div class="font-bold text-indigo-700 text-sm">Lvl 71-85</div>
                    <div class="text-xs text-gray-400 mt-1">Horizon Master</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-white border border-purple-100 shadow-sm">
                    <div class="text-2xl mb-2">🌟</div>
                    <div class="font-bold text-purple-700 text-sm">Lvl 86-99</div>
                    <div class="text-xs text-gray-400 mt-1">Elite Explorer</div>
                </div>
                <div class="card-hover text-center p-4 rounded-2xl bg-gradient-to-b from-amber-50 to-white border border-amber-200 shadow-sm">
                    <div class="text-2xl mb-2">👑</div>
                    <div class="font-bold text-amber-700 text-sm">Lvl 100</div>
                    <div class="text-xs text-gray-400 mt-1">Lakbay Legend</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Rewards Section -->
    <section id="rewards" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">Redeem Rewards</h2>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">Badges earn you points. Trade those points for exclusive freebies — merch, discounts, free trips, and more.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
                @php
                    $rewards = \App\Models\Reward::where('is_active', true)->orderBy('points_cost')->take(6)->get();
                @endphp
                @forelse($rewards as $reward)
                    <div class="card-hover rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm">
                        <div class="h-32 bg-gradient-to-br from-rose-50 to-amber-50 flex items-center justify-center">
                            <span class="text-4xl">🎁</span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 mb-1">{{ $reward->name }}</h3>
                            <p class="text-xs text-gray-500 mb-3">{{ Str::limit($reward->description, 60) }}</p>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-sm font-bold text-amber-700">
                                    ⭐ {{ number_format($reward->points_cost) }} pts
                                </span>
                                <span class="text-xs text-gray-400">{{ $reward->availableStock() }} left</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <span class="text-5xl mb-4 block">🎁</span>
                        <p class="text-gray-500">Rewards coming soon! Earn badges to collect points.</p>
                    </div>
                @endforelse
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('rewards.page') }}" class="inline-flex items-center gap-2 px-8 py-3 text-sm font-bold text-white rounded-full shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300" style="background: linear-gradient(135deg, #f59e0b, #f97316);">
                    🎁 View All Rewards
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Download the App -->
    <section id="download" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto rounded-3xl bg-gradient-to-br from-gray-900 to-gray-800 overflow-hidden shadow-2xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                    <!-- Left content -->
                    <div class="p-10 lg:p-14 flex flex-col justify-center">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium mb-6 w-fit">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Available on iOS & Android
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">
                            Get the LakbayXP App
                        </h2>
                        <p class="text-gray-400 leading-relaxed mb-8">
                            Book events, unlock places, earn XP, collect badges, and redeem rewards — all from your phone. Download now and start your adventure at Level 1.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <!-- App Store button -->
                            <a href="#" class="inline-flex items-center gap-3 px-6 py-3 bg-white rounded-xl hover:bg-gray-100 transition-colors">
                                <svg class="w-7 h-7 text-gray-900" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                </svg>
                                <div>
                                    <div class="text-[10px] text-gray-500 leading-none">Download on the</div>
                                    <div class="text-sm font-bold text-gray-900 leading-tight">App Store</div>
                                </div>
                            </a>
                            <!-- Google Play button -->
                            <a href="#" class="inline-flex items-center gap-3 px-6 py-3 bg-white rounded-xl hover:bg-gray-100 transition-colors">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                                    <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92z" fill="#4285F4"/>
                                    <path d="M17.556 8.236L5.178.923C4.678.636 4.11.56 3.609 1.814L13.792 12l3.764-3.764z" fill="#EA4335"/>
                                    <path d="M3.609 22.186c.501 1.254 1.069 1.178 1.569.891l12.378-7.313L13.792 12 3.609 22.186z" fill="#34A853"/>
                                    <path d="M20.834 10.312l-3.278-1.94L13.792 12l3.764 3.764 3.278-1.94c.904-.536.904-2.976 0-3.512z" fill="#FBBC05"/>
                                </svg>
                                <div>
                                    <div class="text-[10px] text-gray-500 leading-none">Get it on</div>
                                    <div class="text-sm font-bold text-gray-900 leading-tight">Google Play</div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <!-- Right — phone mockup -->
                    <div class="hidden lg:flex items-center justify-center p-10 relative">
                        <div class="w-56 h-[420px] rounded-[2.5rem] bg-gradient-to-b from-emerald-500 to-cyan-500 p-1 shadow-2xl shadow-emerald-500/20">
                            <div class="w-full h-full rounded-[2.2rem] bg-gray-900 flex flex-col items-center justify-center p-6 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <div class="text-white font-bold text-lg mb-1">LakbayXP</div>
                                <div class="text-gray-400 text-xs mb-6">Level up your adventure</div>
                                <div class="w-full space-y-2">
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 text-left">
                                        <span class="text-sm">⛰️</span>
                                        <span class="text-xs text-gray-300">Book Events</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 text-left">
                                        <span class="text-sm">⚡</span>
                                        <span class="text-xs text-gray-300">Earn XP & Level Up</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 text-left">
                                        <span class="text-sm">🏆</span>
                                        <span class="text-xs text-gray-300">Collect Badges</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 text-left">
                                        <span class="text-sm">🎁</span>
                                        <span class="text-xs text-gray-300">Redeem Rewards</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Leaderboard Preview -->
    <section id="leaderboard" class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">Top Explorers</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">The highest-level adventurers in the community.</p>
            </div>
            <div class="max-w-2xl mx-auto">
                @php
                    $topExplorers = \App\Models\User::withCount(['unlockedPlaces', 'badges'])
                        ->orderByDesc('level')->orderByDesc('xp')
                        ->take(5)->get();
                    $rankColors = ['bg-amber-400', 'bg-gray-300', 'bg-amber-600', 'bg-gray-200', 'bg-gray-200'];
                @endphp
                @foreach($topExplorers as $index => $explorer)
                    <div class="flex items-center gap-4 p-4 {{ $index === 0 ? 'bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-100' : 'bg-white border border-gray-100' }} rounded-xl mb-3 card-hover">
                        <div class="w-8 h-8 rounded-full {{ $rankColors[$index] ?? 'bg-gray-200' }} flex items-center justify-center text-sm font-bold {{ $index === 0 ? 'text-white' : 'text-gray-700' }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-sm font-extrabold text-white">
                            {{ $explorer->level }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">{{ $explorer->username }}</div>
                            <div class="text-xs text-gray-400">{{ number_format($explorer->xp) }} XP · {{ $explorer->unlocked_places_count }} places</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold gradient-text">Lvl {{ $explorer->level }}</div>
                            <div class="text-xs text-gray-400">{{ $explorer->badges_count }} badges</div>
                        </div>
                    </div>
                @endforeach
                <div class="text-center mt-8">
                    <a href="{{ route('leaderboard') }}" class="inline-flex items-center gap-2 px-8 py-3 text-sm font-bold text-white rounded-full shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300" style="background: linear-gradient(135deg, #059669, #0891b2);">
                        🏆 View Full Leaderboard
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-br from-emerald-600 via-cyan-600 to-indigo-600 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 text-9xl">⛰️</div>
            <div class="absolute bottom-10 right-10 text-9xl">🏝️</div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-9xl">🌊</div>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight mb-6">
                Ready to start your journey?
            </h2>
            <p class="text-lg text-emerald-100 max-w-2xl mx-auto mb-10">
                Join thousands of Filipino explorers. Earn XP, collect badges, redeem rewards, and level up to 100.
            </p>
            <a href="#download" class="inline-flex items-center gap-2 px-10 py-4 text-lg font-bold text-emerald-700 bg-white rounded-full hover:bg-emerald-50 transition-all shadow-xl hover:shadow-2xl">
                Download the App
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-gray-900 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">Lakbay<span class="text-emerald-400">XP</span></span>
                    </div>
                    <p class="text-sm leading-relaxed max-w-sm">The Philippines' travel adventure community. Discover trips, earn XP, collect badges, redeem rewards, and level up your explorer profile.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Explore</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#events" class="hover:text-emerald-400 transition-colors">Upcoming Events</a></li>
                        <li><a href="#places" class="hover:text-emerald-400 transition-colors">Destinations</a></li>
                        <li><a href="{{ route('rewards.page') }}" class="hover:text-emerald-400 transition-colors">Rewards</a></li>
                        <li><a href="{{ route('leaderboard') }}" class="hover:text-emerald-400 transition-colors">Leaderboard</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Platform</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition-colors">Organizer Portal</a></li>
                        <li><a href="#download" class="hover:text-emerald-400 transition-colors">Download App</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 pt-8 border-t border-gray-800 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} LakbayXP. All rights reserved. Made with ❤️ in the Philippines.
            </div>
        </div>
    </footer>

</body>
</html>
