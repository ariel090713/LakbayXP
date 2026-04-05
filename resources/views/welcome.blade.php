<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LakbayXP') }} — Discover. Explore. Conquer.</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                    <a href="#rewards" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Rewards</a>
                    <a href="#leaderboard" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors">Leaderboard</a>
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

    <!-- How It Works -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">How LakbayXP Works</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">Your journey from Level 1 to Level 100.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                @php
                    $steps = [
                        ['icon' => '🔍', 'title' => 'Discover', 'desc' => 'Browse events organized by verified leaders.', 'color' => 'emerald'],
                        ['icon' => '🎫', 'title' => 'Book', 'desc' => 'Reserve your slot. Limited spots per trip.', 'color' => 'cyan'],
                        ['icon' => '🏔️', 'title' => 'Conquer', 'desc' => 'Complete the adventure and unlock the place.', 'color' => 'indigo'],
                        ['icon' => '⚡', 'title' => 'Earn XP', 'desc' => 'Get badges, XP, and points. Level up!', 'color' => 'purple'],
                        ['icon' => '🎁', 'title' => 'Redeem', 'desc' => 'Trade points for exclusive freebies.', 'color' => 'rose'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                    <div class="text-center">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-{{ $step['color'] }}-50 border border-{{ $step['color'] }}-100 flex items-center justify-center float-animation" style="animation-delay: {{ $i * -1.2 }}s">
                            <span class="text-2xl">{{ $step['icon'] }}</span>
                        </div>
                        <h3 class="text-base font-bold mb-1">{{ $step['title'] }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
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
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-2">Popular Destinations</h2>
                <p class="text-gray-500">Places our community loves the most.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $featuredPlaces = \App\Models\Place::where('is_active', true)
                        ->withCount('unlockedByUsers')
                        ->orderByDesc('unlocked_by_users_count')
                        ->take(6)->get();
                @endphp
                @foreach($featuredPlaces as $place)
                    <div class="card-hover group rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm">
                        <div class="h-44 bg-gradient-to-br from-emerald-100 to-cyan-50 flex items-center justify-center relative overflow-hidden">
                            <span class="text-6xl opacity-60">{{ $categoryIcons[$place->category->value] ?? '📍' }}</span>
                            <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur text-xs font-semibold text-emerald-700 border border-emerald-100">
                                {{ $categoryLabels[$place->category->value] ?? $place->category->value }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $place->name }}</h3>
                            <p class="text-sm text-gray-500 mb-3">{{ $place->province ?? $place->region ?? 'Philippines' }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-sm text-emerald-600 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $place->unlocked_by_users_count }} conquered
                                </div>
                                @if($place->category->value === 'mountain' && isset($place->category_fields['meters_above_sea_level']))
                                    <span class="text-xs text-gray-400 font-medium">{{ number_format($place->category_fields['meters_above_sea_level']) }} masl</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Upcoming Events -->
    <section id="events" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold tracking-tight mb-4">Upcoming Adventures</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">Join organized trips led by verified community organizers.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $upcomingEvents = \App\Models\Event::whereIn('status', ['published', 'full'])
                        ->where('event_date', '>=', now())
                        ->with(['place', 'organizer'])
                        ->orderBy('event_date')->take(6)->get();
                @endphp
                @forelse($upcomingEvents as $event)
                    <div class="card-hover rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm">
                        <div class="h-40 bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center relative">
                            <span class="text-5xl opacity-50">{{ $categoryIcons[$event->category->value] ?? '🎯' }}</span>
                            @if($event->status->value === 'full')
                                <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-amber-100 text-xs font-bold text-amber-700">FULL</div>
                            @endif
                            <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur text-xs font-semibold text-gray-700">{{ $event->event_date->format('M d') }}</div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-500 mb-3 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $event->place->name ?? 'TBA' }}
                            </p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700">{{ substr($event->organizer->name ?? 'O', 0, 1) }}</div>
                                    <span class="text-xs text-gray-500">{{ $event->organizer->name ?? 'Organizer' }}</span>
                                    @if($event->organizer->is_verified_organizer ?? false)
                                        <svg class="w-3.5 h-3.5 text-cyan-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-emerald-600">₱{{ number_format($event->fee, 0) }}</div>
                                    <div class="text-xs text-gray-400">{{ $event->availableSlots() }}/{{ $event->max_slots }} slots</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <span class="text-5xl mb-4 block">🏕️</span>
                        <p class="text-gray-500">No upcoming events yet. Check back soon!</p>
                    </div>
                @endforelse
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
                        <li><a href="#rewards" class="hover:text-emerald-400 transition-colors">Rewards</a></li>
                        <li><a href="#leaderboard" class="hover:text-emerald-400 transition-colors">Leaderboard</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Platform</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition-colors">Organizer Login</a></li>
                        @if(Route::has('register'))
                            <li><a href="{{ route('register') }}" class="hover:text-emerald-400 transition-colors">Become an Organizer</a></li>
                        @endif
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
