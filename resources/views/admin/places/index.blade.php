<x-layouts.admin>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Places</h1>
            <a href="{{ route('admin.places.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-xl hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-md shadow-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Place
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <!-- Search & Filters -->
        <form method="GET" action="{{ route('admin.places.index') }}" class="bg-white rounded-2xl border border-gray-100 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, province, region..."
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                </div>

                <!-- Category -->
                <div>
                    <select name="category" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">All Categories</option>
                        @foreach(\App\Enums\PlaceCategory::cases() as $cat)
                            <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $cat->value)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Region -->
                <div>
                    <select name="region" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">All Regions</option>
                        @foreach($regions as $r)
                            <option value="{{ $r }}" {{ request('region') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg hover:bg-gray-900 transition-colors">Filter</button>
                    <a href="{{ route('admin.places.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Clear</a>
                </div>
            </div>
        </form>

        <!-- Results count -->
        <div class="text-xs text-gray-500">
            Showing {{ $places->firstItem() ?? 0 }}–{{ $places->lastItem() ?? 0 }} of {{ $places->total() }} places
            @if(request()->hasAny(['search', 'category', 'region', 'province', 'status']))
                (filtered)
            @endif
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Place</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Region</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Province</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">XP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $icons = ['mountain'=>'⛰️','beach'=>'🏖️','island'=>'🏝️','falls'=>'💧','river'=>'🌊','lake'=>'🏞️','campsite'=>'⛺','historical'=>'🏛️','food_destination'=>'🍜','road_trip'=>'🚗','hidden_gem'=>'💎'];
                    @endphp
                    @forelse($places as $place)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ $icons[$place->category->value] ?? '📍' }}</span>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $place->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $place->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $place->category->value)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $place->region ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $place->province ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-emerald-600">{{ $place->xp_reward ?? 0 }}</td>
                            <td class="px-4 py-3">
                                @if($place->is_active)
                                    <span class="px-2 py-1 rounded-full bg-emerald-50 text-xs font-medium text-emerald-700">Active</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-red-50 text-xs font-medium text-red-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.places.edit', $place) }}" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-medium text-gray-700 hover:bg-gray-200 transition-colors">Edit</a>
                                    @if($place->is_active)
                                        <form method="POST" action="{{ route('admin.places.destroy', $place) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-red-50 text-xs font-medium text-red-600 hover:bg-red-100 transition-colors">Deactivate</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.places.activate', $place) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-50 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">Activate</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <span class="text-3xl block mb-2">📍</span>
                                <p class="text-sm text-gray-400">No places found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $places->links() }}
    </div>
</x-layouts.admin>
