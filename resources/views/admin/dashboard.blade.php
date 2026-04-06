<x-layouts.admin>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Overview of your LakbayXP platform.</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">{{ $totalPlaces }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">Places</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-lg">📍</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">{{ $totalEvents }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">Events</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-lg">📅</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">{{ $totalUsers }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">Users</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-lg">👥</div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-3xl font-extrabold text-gray-900">{{ $totalOrganizers }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">Organizers</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-lg">🎯</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.places.create') }}" class="group bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md hover:border-emerald-200 transition-all">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center text-lg mb-3 transition-colors">⛰️</div>
                <div class="font-semibold text-sm text-gray-900">Add Place</div>
                <p class="text-xs text-gray-400 mt-1">Create a new destination</p>
            </a>
            <a href="{{ route('admin.badges.create') }}" class="group bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md hover:border-cyan-200 transition-all">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 group-hover:bg-cyan-100 flex items-center justify-center text-lg mb-3 transition-colors">🏆</div>
                <div class="font-semibold text-sm text-gray-900">Add Badge</div>
                <p class="text-xs text-gray-400 mt-1">Create achievement badge</p>
            </a>
            <a href="{{ route('admin.rewards.create') }}" class="group bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md hover:border-indigo-200 transition-all">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center text-lg mb-3 transition-colors">🎁</div>
                <div class="font-semibold text-sm text-gray-900">Add Reward</div>
                <p class="text-xs text-gray-400 mt-1">Create redeemable freebie</p>
            </a>
            <a href="{{ route('admin.organizers.index') }}" class="group bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md hover:border-amber-200 transition-all">
                <div class="w-10 h-10 rounded-xl bg-amber-50 group-hover:bg-amber-100 flex items-center justify-center text-lg mb-3 transition-colors">✅</div>
                <div class="font-semibold text-sm text-gray-900">Verify Organizers</div>
                <p class="text-xs text-gray-400 mt-1">Review pending accounts</p>
            </a>
        </div>
    </div>
</x-layouts.admin>
