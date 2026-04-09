<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Your Profile — PinasLakbay</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #059669, #0891b2, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 antialiased">
    <div class="max-w-2xl mx-auto px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">Welcome to <span class="gradient-text">PinasLakbay</span></h1>
            <p class="text-gray-500 mt-2">Let's set up your organizer profile. This helps explorers trust your events.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organizer.onboarding.store') }}" class="space-y-8">
            @csrf

            <!-- Step 1: Organizer Type -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-1">What type of organizer are you?</h2>
                <p class="text-sm text-gray-500 mb-5">This helps us tailor your experience.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="organizer_type" value="solo" class="peer sr-only" {{ old('organizer_type') === 'solo' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 border-gray-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-center">
                            <span class="text-3xl block mb-2">🧑</span>
                            <span class="font-semibold text-sm text-gray-900">Solo Guide</span>
                            <p class="text-xs text-gray-500 mt-1">Independent trip organizer</p>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="organizer_type" value="agency" class="peer sr-only" {{ old('organizer_type') === 'agency' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 border-gray-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-center">
                            <span class="text-3xl block mb-2">🏢</span>
                            <span class="font-semibold text-sm text-gray-900">Travel Agency</span>
                            <p class="text-xs text-gray-500 mt-1">Registered travel business</p>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="organizer_type" value="organization" class="peer sr-only" {{ old('organizer_type') === 'organization' ? 'checked' : '' }}>
                        <div class="p-4 rounded-xl border-2 border-gray-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-center">
                            <span class="text-3xl block mb-2">👥</span>
                            <span class="font-semibold text-sm text-gray-900">Community / Club</span>
                            <p class="text-xs text-gray-500 mt-1">Hiking club, travel group</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 2: Basic Info -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Your Details</h2>
                <p class="text-sm text-gray-500 mb-5">Tell explorers about yourself.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Organization / Brand Name <span class="text-gray-400">(optional for solo)</span></label>
                        <input type="text" name="organization_name" value="{{ old('organization_name') }}" placeholder="e.g. Summit Seekers PH"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">About You / Your Organization</label>
                        <textarea name="organizer_bio" rows="3" placeholder="Tell explorers what makes your trips special..." required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none">{{ old('organizer_bio') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Max 500 characters</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Specialties -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Your Specialties</h2>
                <p class="text-sm text-gray-500 mb-5">What kind of adventures do you organize? Select all that apply.</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @php
                        $specialtyOptions = [
                            'mountain' => ['icon' => '⛰️', 'label' => 'Mountains'],
                            'beach' => ['icon' => '🏖️', 'label' => 'Beaches'],
                            'island' => ['icon' => '🏝️', 'label' => 'Islands'],
                            'falls' => ['icon' => '💧', 'label' => 'Waterfalls'],
                            'river' => ['icon' => '🌊', 'label' => 'Rivers'],
                            'lake' => ['icon' => '🏞️', 'label' => 'Lakes'],
                            'campsite' => ['icon' => '⛺', 'label' => 'Camping'],
                            'historical' => ['icon' => '🏛️', 'label' => 'Historical'],
                            'food_destination' => ['icon' => '🍜', 'label' => 'Food Trips'],
                            'road_trip' => ['icon' => '🚗', 'label' => 'Road Trips'],
                            'hidden_gem' => ['icon' => '💎', 'label' => 'Hidden Gems'],
                            'city_tour' => ['icon' => '🏙️', 'label' => 'City Tours'],
                        ];
                        $oldSpecialties = old('specialties', []);
                    @endphp
                    @foreach($specialtyOptions as $key => $opt)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="specialties[]" value="{{ $key }}" class="peer sr-only" {{ in_array($key, $oldSpecialties) ? 'checked' : '' }}>
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 border-gray-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all">
                                <span class="text-lg">{{ $opt['icon'] }}</span>
                                <span class="text-sm font-medium text-gray-700">{{ $opt['label'] }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 4: Social Links -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Social Links <span class="text-gray-400 font-normal text-sm">(optional)</span></h2>
                <p class="text-sm text-gray-500 mb-5">Help explorers find you online.</p>

                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                            <span class="text-lg">📘</span>
                        </div>
                        <input type="url" name="social_facebook" value="{{ old('social_facebook') }}" placeholder="https://facebook.com/yourpage"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center shrink-0">
                            <span class="text-lg">📸</span>
                        </div>
                        <input type="text" name="social_instagram" value="{{ old('social_instagram') }}" placeholder="@yourhandle"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                            <span class="text-lg">🌐</span>
                        </div>
                        <input type="url" name="social_website" value="{{ old('social_website') }}" placeholder="https://yourwebsite.com"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-400">You can update these later in settings.</p>
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-xl hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-lg shadow-emerald-500/20">
                    Complete Setup
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
