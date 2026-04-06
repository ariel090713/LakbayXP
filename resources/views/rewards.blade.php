<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rewards — LakbayXP</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #059669, #0891b2, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .float-animation { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }
        .shimmer { background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%); background-size: 200% 100%; animation: shimmer 3s infinite; }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        @keyframes pulse-glow { 0%,100% { box-shadow: 0 0 20px rgba(251,191,36,0.2); } 50% { box-shadow: 0 0 40px rgba(251,191,36,0.5); } }
    </style>
</head>
<body class="min-h-screen bg-gray-50 antialiased">

    @php
        $rewards = \App\Models\Reward::where('is_active', true)->orderBy('points_cost')->get();
        $badges = \App\Models\Badge::where('is_active', true)->orderBy('points', 'desc')->get();
        $totalRedemptions = \App\Models\RewardRedemption::count();
        $totalPointsSpent = \App\Models\RewardRedemption::whereIn('status', ['approved','claimed'])->sum('points_spent');
        $topRedeemers = \App\Models\User::where('role', 'user')
            ->withCount('redemptions')
            ->having('redemptions_count', '>', 0)
            ->orderByDesc('redemptions_count')
            ->take(5)->get();
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
    <section class="pt-24 pb-10 relative overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 40%, #ef4444 100%);">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-8 left-[10%] text-8xl float-animation">🎁</div>
            <div class="absolute top-12 right-[12%] text-6xl float-animation" style="animation-delay:-2s">⭐</div>
            <div class="absolute bottom-4 left-[20%] text-5xl float-animation" style="animation-delay:-4s">🏅</div>
            <div class="absolute bottom-8 right-[8%] text-7xl float-animation" style="animation-delay:-3s">🎉</div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center text-white">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/15 backdrop-blur text-sm font-bold uppercase tracking-wider mb-4">🎁 Rewards Store</span>
                <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight mb-3">Redeem Rewards</h1>
                <p class="text-white/70 text-lg max-w-lg mx-auto">Earn badges → collect points → trade for freebies. The more you explore, the more you earn.</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-10 max-w-3xl mx-auto">
                <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur border border-white/10">
                    <div class="text-2xl mb-1">🎁</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-white">{{ $rewards->count() }}</div>
                    <div class="text-[10px] font-semibold text-white/50 uppercase tracking-wider mt-1">Rewards</div>
                </div>
                <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur border border-white/10">
                    <div class="text-2xl mb-1">🏅</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-white">{{ $badges->count() }}</div>
                    <div class="text-[10px] font-semibold text-white/50 uppercase tracking-wider mt-1">Badges</div>
                </div>
                <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur border border-white/10">
                    <div class="text-2xl mb-1">🔄</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-white">{{ number_format($totalRedemptions) }}</div>
                    <div class="text-[10px] font-semibold text-white/50 uppercase tracking-wider mt-1">Redeemed</div>
                </div>
                <div class="text-center p-4 rounded-2xl bg-white/10 backdrop-blur border border-white/10">
                    <div class="text-2xl mb-1">⭐</div>
                    <div class="text-xl sm:text-2xl font-extrabold text-white">{{ number_format($totalPointsSpent) }}</div>
                    <div class="text-[10px] font-semibold text-white/50 uppercase tracking-wider mt-1">Points Spent</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How Points Work -->
    <section class="py-14 bg-white border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-extrabold text-gray-900 mb-2">How Points Work</h2>
                <p class="text-gray-400 text-sm">Badges give you points. Points buy you rewards.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="text-center p-6 rounded-2xl bg-gradient-to-b from-amber-50 to-white border border-amber-100">
                    <div class="text-4xl mb-3 float-animation">🏔️</div>
                    <h3 class="font-extrabold text-gray-900 mb-1">Explore Places</h3>
                    <p class="text-xs text-gray-500">Complete events and unlock places across the Philippines.</p>
                </div>
                <div class="text-center p-6 rounded-2xl bg-gradient-to-b from-amber-50 to-white border border-amber-100">
                    <div class="text-4xl mb-3 float-animation" style="animation-delay:-2s">🏅</div>
                    <h3 class="font-extrabold text-gray-900 mb-1">Earn Badges</h3>
                    <p class="text-xs text-gray-500">Hit milestones to earn badges. Each badge gives you redeemable points.</p>
                </div>
                <div class="text-center p-6 rounded-2xl bg-gradient-to-b from-amber-50 to-white border border-amber-100">
                    <div class="text-4xl mb-3 float-animation" style="animation-delay:-4s">🎁</div>
                    <h3 class="font-extrabold text-gray-900 mb-1">Redeem Rewards</h3>
                    <p class="text-xs text-gray-500">Trade your points for merch, discounts, free trips, and exclusive freebies.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Rewards -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-900">Available Rewards</h2>
                    <p class="text-sm text-gray-400 mt-1">Redeem via the LakbayXP mobile app.</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">{{ $rewards->count() }} rewards</span>
            </div>

            @if($rewards->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($rewards as $ri => $reward)
                        @php
                            $gradients = [
                                'from-rose-50 to-amber-50',
                                'from-amber-50 to-yellow-50',
                                'from-emerald-50 to-cyan-50',
                                'from-indigo-50 to-purple-50',
                                'from-cyan-50 to-blue-50',
                                'from-pink-50 to-rose-50',
                            ];
                            $grad = $gradients[$ri % count($gradients)];
                            $stock = $reward->availableStock();
                            $isLow = $stock > 0 && $stock <= 3;
                            $isOut = $stock === 0;
                        @endphp
                        <div class="card-hover rounded-2xl bg-white border border-gray-100 overflow-hidden shadow-sm relative group {{ $isOut ? 'opacity-60' : '' }}">
                            {{-- Low stock badge --}}
                            @if($isLow)
                                <div class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full bg-red-500 text-white text-[10px] font-bold animate-pulse">🔥 {{ $stock }} left</div>
                            @endif
                            @if($isOut)
                                <div class="absolute top-3 left-3 z-10 px-2 py-0.5 rounded-full bg-gray-500 text-white text-[10px] font-bold">Sold Out</div>
                            @endif

                            {{-- Image area --}}
                            <div class="h-40 bg-gradient-to-br {{ $grad }} flex items-center justify-center relative overflow-hidden">
                                @if($reward->image_path)
                                    <img src="{{ Storage::url($reward->image_path) }}" class="w-full h-full object-cover" alt="{{ $reward->name }}">
                                @else
                                    <span class="text-5xl group-hover:scale-110 transition-transform duration-300">🎁</span>
                                @endif
                                {{-- Points badge --}}
                                <div class="absolute bottom-3 right-3 px-3 py-1.5 rounded-xl bg-white/90 backdrop-blur shadow-md border border-amber-200 pulse-glow">
                                    <span class="text-sm font-extrabold text-amber-700">⭐ {{ number_format($reward->points_cost) }}</span>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-5">
                                <h3 class="font-extrabold text-gray-900 mb-1 group-hover:text-amber-700 transition-colors">{{ $reward->name }}</h3>
                                <p class="text-xs text-gray-500 leading-relaxed mb-4">{{ $reward->description ?? 'Exclusive reward for LakbayXP explorers.' }}</p>

                                <div class="flex items-center justify-between">
                                    @if(!$isOut)
                                        <span class="text-[10px] font-semibold text-gray-400">{{ $stock }} in stock</span>
                                        <span class="px-3 py-1 rounded-lg text-[10px] font-bold text-white" style="background: linear-gradient(135deg, #f59e0b, #f97316);">Redeem in App</span>
                                    @else
                                        <span class="text-[10px] font-semibold text-gray-400">Out of stock</span>
                                        <span class="px-3 py-1 rounded-lg bg-gray-100 text-[10px] font-bold text-gray-400">Unavailable</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-2xl border border-gray-100">
                    <span class="text-6xl block mb-4">🎁</span>
                    <h3 class="text-xl font-extrabold text-gray-900 mb-2">Rewards coming soon</h3>
                    <p class="text-gray-400">Start earning badges now — rewards will be available soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Badges That Earn Points -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Badges That Earn Points</h2>
                <p class="text-gray-400 text-sm">Complete challenges to earn these badges. Each badge gives you points for the rewards store.</p>
            </div>

            @if($badges->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($badges as $bi => $badge)
                        @php
                            $badgeColors = ['emerald','cyan','indigo','purple','rose','amber','teal','blue','violet','orange'];
                            $bc = $badgeColors[$bi % count($badgeColors)];
                            $earnedCount = $badge->users()->count();
                        @endphp
                        <div class="card-hover text-center p-5 rounded-2xl bg-{{ $bc }}-50 border border-{{ $bc }}-100 relative group">
                            {{-- Points value --}}
                            <div class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-white/80 text-[10px] font-bold text-amber-700 border border-amber-200">⭐ {{ $badge->points ?? 0 }}</div>

                            {{-- Icon --}}
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-{{ $bc }}-100 border border-{{ $bc }}-200 flex items-center justify-center float-animation" style="animation-delay: {{ $bi * -0.8 }}s">
                                @if($badge->icon_path)
                                    <img src="{{ Storage::url($badge->icon_path) }}" class="w-8 h-8" alt="">
                                @else
                                    <span class="text-2xl">🏅</span>
                                @endif
                            </div>

                            <h3 class="text-sm font-extrabold text-gray-900 mb-1">{{ $badge->name }}</h3>
                            <p class="text-[10px] text-gray-500 leading-relaxed mb-2">{{ Str::limit($badge->description, 50) }}</p>

                            {{-- Earned count --}}
                            <div class="text-[10px] font-semibold text-{{ $bc }}-600">{{ $earnedCount }} earned</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <span class="text-5xl block mb-3">🏅</span>
                    <p class="text-gray-400">Badges coming soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 50%, #ef4444 100%);">
        <div class="max-w-2xl mx-auto px-4 text-center text-white">
            <span class="text-5xl block mb-4">🎁</span>
            <h2 class="text-3xl font-extrabold tracking-tight mb-3">Start earning rewards today</h2>
            <p class="text-white/70 mb-6">Download LakbayXP, explore the Philippines, earn badges, and redeem exclusive freebies.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('home') }}#download" class="inline-flex items-center gap-2 px-10 py-4 text-base font-bold text-amber-700 bg-white rounded-full hover:bg-amber-50 transition-all shadow-xl">
                    Download the App
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('leaderboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white/90 border-2 border-white/30 rounded-full hover:bg-white/10 transition-all">
                    🏆 View Leaderboard
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 bg-gray-900 text-center">
        <a href="{{ route('home') }}" class="text-white/60 text-sm hover:text-white transition-colors">← Back to LakbayXP</a>
    </footer>

</body>
</html>
