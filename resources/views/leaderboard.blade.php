<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leaderboard — LakbayXP</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #059669, #0891b2, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .float-animation { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .podium-rise { animation: podium-rise 0.8s ease-out forwards; opacity: 0; transform: translateY(40px); }
        @keyframes podium-rise { to { opacity: 1; transform: translateY(0); } }
        .rank-glow { animation: rank-glow 2s ease-in-out infinite; }
        @keyframes rank-glow { 0%,100% { box-shadow: 0 0 20px rgba(251,191,36,0.3); } 50% { box-shadow: 0 0 40px rgba(251,191,36,0.6); } }
        .xp-bar-fill { animation: xp-fill 1.5s ease-out forwards; }
        @keyframes xp-fill { from { width: 0; } }
        .row-enter { animation: row-slide 0.5s ease-out forwards; opacity: 0; transform: translateX(-20px); }
        @keyframes row-slide { to { opacity: 1; transform: translateX(0); } }
        .pulse-badge { animation: pulse-badge 2s ease-in-out infinite; }
        @keyframes pulse-badge { 0%,100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        .shimmer { background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%); background-size: 200% 100%; animation: shimmer 2s infinite; }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
    </style>
</head>
<body class="min-h-screen bg-gray-50 antialiased">

    @php
        $tiers = [
            ['min'=>1,'max'=>5,'name'=>'Newbie','icon'=>'🌱','color'=>'gray'],
            ['min'=>6,'max'=>15,'name'=>'Wanderer','icon'=>'🥾','color'=>'emerald'],
            ['min'=>16,'max'=>25,'name'=>'Adventurer','icon'=>'🧭','color'=>'teal'],
            ['min'=>26,'max'=>35,'name'=>'Pathfinder','icon'=>'🗺️','color'=>'cyan'],
            ['min'=>36,'max'=>45,'name'=>'Trail Blazer','icon'=>'🔥','color'=>'blue'],
            ['min'=>46,'max'=>55,'name'=>'Explorer','icon'=>'⛰️','color'=>'indigo'],
            ['min'=>56,'max'=>65,'name'=>'Voyager','icon'=>'🚀','color'=>'violet'],
            ['min'=>66,'max'=>75,'name'=>'Conqueror','icon'=>'⚔️','color'=>'purple'],
            ['min'=>76,'max'=>85,'name'=>'Master','icon'=>'🌟','color'=>'amber'],
            ['min'=>86,'max'=>99,'name'=>'Elite','icon'=>'💎','color'=>'rose'],
            ['min'=>100,'max'=>100,'name'=>'Summit Legend','icon'=>'👑','color'=>'yellow'],
        ];

        function getTier($level, $tiers) {
            foreach ($tiers as $t) {
                if ($level >= $t['min'] && $level <= $t['max']) return $t;
            }
            return $tiers[0];
        }

        $topExplorers = \App\Models\User::where('role', 'user')
            ->withCount(['unlockedPlaces', 'badges'])
            ->where('xp', '>', 0)
            ->orderByDesc('level')
            ->orderByDesc('xp')
            ->take(50)
            ->get();

        $totalExplorers = \App\Models\User::where('role', 'user')->count();
        $totalXpEarned = \App\Models\User::where('role', 'user')->sum('xp');
        $totalPlacesUnlocked = \App\Models\PlaceUnlock::count();
        $totalBadgesEarned = \DB::table('user_badges')->count();

        $xpService = app(\App\Services\XpService::class);
    @endphp

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #059669, #0891b2);">
                    <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                </div>
                <span class="text-lg font-extrabold text-gray-900">Lakbay<span class="gradient-text">XP</span></span>
            </a>
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">← Back to Home</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="pt-24 pb-8 relative overflow-hidden" style="background: linear-gradient(135deg, #059669 0%, #0891b2 40%, #6366f1 100%);">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-8 left-[10%] text-8xl float-animation">🏆</div>
            <div class="absolute top-12 right-[15%] text-6xl float-animation" style="animation-delay:-2s">⚡</div>
            <div class="absolute bottom-4 left-[20%] text-5xl float-animation" style="animation-delay:-4s">🏔️</div>
            <div class="absolute bottom-8 right-[10%] text-7xl float-animation" style="animation-delay:-3s">👑</div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center text-white">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/15 backdrop-blur text-sm font-bold uppercase tracking-wider mb-4">🏆 Hall of Fame</span>
                <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight mb-3">Leaderboard</h1>
                <p class="text-white/70 text-lg max-w-lg mx-auto">The bravest explorers of the Philippines. Where do you rank?</p>
            </div>

            <!-- Global Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-10 max-w-3xl mx-auto">
                @php
                    $globalStats = [
                        ['value' => number_format($totalExplorers), 'label' => 'Explorers', 'icon' => '👥'],
                        ['value' => number_format($totalXpEarned), 'label' => 'Total XP Earned', 'icon' => '⚡'],
                        ['value' => number_format($totalPlacesUnlocked), 'label' => 'Places Unlocked', 'icon' => '🔓'],
                        ['value' => number_format($totalBadgesEarned), 'label' => 'Badges Earned', 'icon' => '🏅'],
                    ];
                @endphp
                @foreach($globalStats as $gs)
                    <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur border border-white/10">
                        <div class="text-2xl mb-1">{{ $gs['icon'] }}</div>
                        <div class="text-xl sm:text-2xl font-extrabold text-white">{{ $gs['value'] }}</div>
                        <div class="text-[10px] font-semibold text-white/50 uppercase tracking-wider mt-1">{{ $gs['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Podium (Top 3) -->
    <section class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($topExplorers->count() >= 3)
                <div class="flex items-end justify-center gap-4 sm:gap-6 mb-12">
                    @php
                        $podiumOrder = [1, 0, 2]; // 2nd, 1st, 3rd
                        $podiumHeights = ['h-36 sm:h-44', 'h-44 sm:h-56', 'h-28 sm:h-36'];
                        $podiumBgs = [
                            'bg-gradient-to-t from-gray-200 to-gray-100 border-gray-200',
                            'bg-gradient-to-t from-amber-200 to-amber-50 border-amber-300 rank-glow',
                            'bg-gradient-to-t from-amber-100 to-orange-50 border-amber-200',
                        ];
                        $crownIcons = ['🥈', '👑', '🥉'];
                        $rankLabels = ['2nd', '1st', '3rd'];
                    @endphp

                    @foreach($podiumOrder as $pi => $rank)
                        @php
                            $user = $topExplorers[$rank];
                            $tier = getTier($user->level, $tiers);
                        @endphp
                        <div class="podium-rise flex flex-col items-center" style="animation-delay: {{ [0.3, 0, 0.5][$pi] }}s">
                            <!-- Avatar + Crown -->
                            <div class="relative mb-3">
                                <div class="text-3xl sm:text-4xl absolute -top-5 left-1/2 -translate-x-1/2 {{ $rank === 0 ? 'pulse-badge' : '' }}">{{ $crownIcons[$pi] }}</div>
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full {{ $rank === 0 ? 'ring-4 ring-amber-400 shadow-xl shadow-amber-400/30' : 'ring-2 ring-gray-200' }} bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-xl sm:text-2xl font-extrabold mt-4">
                                    {{ $user->initials() }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-white shadow flex items-center justify-center text-sm">
                                    {{ $tier['icon'] }}
                                </div>
                            </div>

                            <!-- Name + Stats -->
                            <div class="text-center mb-3">
                                <div class="text-sm sm:text-base font-extrabold text-gray-900 truncate max-w-[120px]">{{ $user->username ?? $user->name }}</div>
                                <div class="text-xs text-gray-400">{{ $tier['name'] }}</div>
                            </div>

                            <!-- XP + Level -->
                            <div class="text-center mb-3">
                                <div class="text-lg sm:text-xl font-extrabold gradient-text">Lvl {{ $user->level }}</div>
                                <div class="text-[10px] font-bold text-gray-400">{{ number_format($user->xp) }} XP</div>
                            </div>

                            <!-- Mini stats -->
                            <div class="flex gap-3 text-[10px] font-semibold text-gray-400 mb-3">
                                <span>🔓 {{ $user->unlocked_places_count }}</span>
                                <span>🏅 {{ $user->badges_count }}</span>
                            </div>

                            <!-- Podium block -->
                            <div class="{{ $podiumHeights[$pi] }} w-24 sm:w-32 {{ $podiumBgs[$pi] }} rounded-t-2xl border border-b-0 flex items-start justify-center pt-3">
                                <span class="text-2xl sm:text-3xl font-extrabold {{ $rank === 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $rankLabels[$pi] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Full Rankings -->
    <section class="pb-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Tier Filter -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-extrabold text-gray-900">All Rankings</h2>
                <div class="text-sm text-gray-400">Top {{ $topExplorers->count() }} explorers</div>
            </div>

            <!-- Rankings List -->
            <div class="space-y-3">
                @foreach($topExplorers as $index => $explorer)
                    @php
                        $tier = getTier($explorer->level, $tiers);
                        $progress = $xpService->getProgress($explorer);
                        $isTop3 = $index < 3;
                        $rankBgs = [
                            0 => 'bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-amber-200',
                            1 => 'bg-gradient-to-r from-gray-50 via-white to-gray-50 border-gray-200',
                            2 => 'bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 border-amber-200',
                        ];
                        $rankIcons = [0 => '🥇', 1 => '🥈', 2 => '🥉'];
                    @endphp
                    <div class="row-enter {{ $isTop3 ? ($rankBgs[$index] ?? '') : 'bg-white border-gray-100' }} border rounded-2xl p-4 sm:p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 group"
                         style="animation-delay: {{ $index * 0.05 }}s">
                        <div class="flex items-center gap-4">
                            <!-- Rank -->
                            <div class="w-10 h-10 rounded-xl {{ $isTop3 ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-md shadow-amber-400/30' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center text-sm font-extrabold shrink-0">
                                @if($isTop3)
                                    {{ $rankIcons[$index] }}
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>

                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-sm font-extrabold {{ $isTop3 ? 'ring-2 ring-amber-300' : '' }}">
                                    {{ $explorer->initials() }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-white shadow flex items-center justify-center text-[10px]">
                                    {{ $tier['icon'] }}
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-extrabold text-gray-900 truncate group-hover:text-emerald-700 transition-colors">{{ $explorer->username ?? $explorer->name }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $tier['color'] }}-50 text-{{ $tier['color'] }}-700">{{ $tier['name'] }}</span>
                                </div>
                                <!-- XP Progress Bar -->
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="xp-bar-fill h-full rounded-full" style="width: {{ $progress['progress_percent'] }}%; background: linear-gradient(90deg, #059669, #0891b2); animation-delay: {{ $index * 0.1 }}s"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-400 shrink-0">{{ $progress['progress_percent'] }}%</span>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="hidden sm:flex items-center gap-4 shrink-0">
                                <div class="text-center">
                                    <div class="text-xs font-extrabold text-gray-900">{{ $explorer->unlocked_places_count }}</div>
                                    <div class="text-[9px] text-gray-400 font-semibold">🔓 Places</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xs font-extrabold text-gray-900">{{ $explorer->badges_count }}</div>
                                    <div class="text-[9px] text-gray-400 font-semibold">🏅 Badges</div>
                                </div>
                            </div>

                            <!-- Level + XP -->
                            <div class="text-right shrink-0">
                                <div class="text-lg font-extrabold gradient-text">{{ $explorer->level }}</div>
                                <div class="text-[10px] font-bold text-gray-400">{{ number_format($explorer->xp) }} XP</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($topExplorers->isEmpty())
                <div class="text-center py-20">
                    <span class="text-6xl block mb-4">🏔️</span>
                    <h3 class="text-xl font-extrabold text-gray-900 mb-2">No explorers yet</h3>
                    <p class="text-gray-400">Be the first to start your adventure!</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Explorer Tiers Reference -->
    <section class="py-16 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Explorer Tiers</h2>
                <p class="text-gray-400 text-sm">Level up to unlock new titles and bragging rights.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach(array_slice($tiers, 0, 6) as $ti => $t)
                    <div class="text-center p-4 rounded-2xl bg-{{ $t['color'] }}-50 border border-{{ $t['color'] }}-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                        <div class="text-3xl mb-2 float-animation" style="animation-delay: {{ $ti * -1 }}s">{{ $t['icon'] }}</div>
                        <div class="text-xs font-extrabold text-gray-900">Lvl {{ $t['min'] }}-{{ $t['max'] }}</div>
                        <div class="text-[10px] font-bold text-{{ $t['color'] }}-600 mt-0.5">{{ $t['name'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mt-3">
                @foreach(array_slice($tiers, 6) as $ti => $t)
                    <div class="text-center p-4 rounded-2xl {{ $t['name'] === 'Summit Legend' ? 'bg-gradient-to-b from-amber-50 to-yellow-50 border-amber-300 rank-glow' : 'bg-'.$t['color'].'-50 border border-'.$t['color'].'-100' }} hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                        <div class="text-3xl mb-2 {{ $t['name'] === 'Summit Legend' ? 'pulse-badge' : 'float-animation' }}" style="animation-delay: {{ ($ti + 6) * -1 }}s">{{ $t['icon'] }}</div>
                        <div class="text-xs font-extrabold text-gray-900">Lvl {{ $t['min'] }}{{ $t['max'] > $t['min'] ? '-'.$t['max'] : '' }}</div>
                        <div class="text-[10px] font-bold text-{{ $t['color'] }}-600 mt-0.5">{{ $t['name'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16" style="background: linear-gradient(135deg, #059669 0%, #0891b2 50%, #6366f1 100%);">
        <div class="max-w-2xl mx-auto px-4 text-center text-white">
            <span class="text-5xl block mb-4">🏔️</span>
            <h2 class="text-3xl font-extrabold tracking-tight mb-3">Ready to climb the ranks?</h2>
            <p class="text-white/70 mb-6">Download LakbayXP, start exploring, and see your name on this leaderboard.</p>
            <a href="{{ route('home') }}#download" class="inline-flex items-center gap-2 px-10 py-4 text-base font-bold text-emerald-700 bg-white rounded-full hover:bg-emerald-50 transition-all shadow-xl">
                Download the App
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 bg-gray-900 text-center">
        <a href="{{ route('home') }}" class="text-white/60 text-sm hover:text-white transition-colors">← Back to LakbayXP</a>
    </footer>

</body>
</html>
