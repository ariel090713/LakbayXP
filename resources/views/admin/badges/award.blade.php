<x-layouts.admin>
    <div class="max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.badges.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Back to Badges</a>
                <h1 class="text-xl font-bold text-gray-900 mt-1">Award Badge: {{ $badge->name }}</h1>
            </div>
            <form method="POST" action="{{ route('admin.badges.award.all', $badge) }}" class="inline">
                @csrf
                <button type="button" onclick="showModal('award-all-modal')" class="px-4 py-2 text-sm font-bold text-white rounded-xl shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">
                    🏅 Award to ALL Users
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 font-medium">✅ {{ session('success') }}</div>
        @endif

        <!-- Badge Info -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-3xl">
                @if($badge->icon_url)
                    <img src="{{ $badge->icon_url }}" class="w-10 h-10" alt="">
                @else
                    🏅
                @endif
            </div>
            <div>
                <div class="font-bold text-gray-900">{{ $badge->name }}</div>
                <div class="text-sm text-gray-500">{{ $badge->description }}</div>
                <div class="flex gap-3 mt-1 text-xs text-gray-400">
                    <span>⭐ {{ $badge->points }} points</span>
                    <span>⚡ {{ $badge->xp_reward }} XP</span>
                    <span>👥 {{ $badge->users()->count() }} awarded</span>
                </div>
            </div>
        </div>

        <!-- Select Users -->
        <form method="POST" action="{{ route('admin.badges.award.store', $badge) }}">
            @csrf
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-gray-900">Select Users</h2>
                    <div class="flex gap-2">
                        <button type="button" onclick="selectAll()" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Select All</button>
                        <button type="button" onclick="deselectAll()" class="text-xs font-medium text-gray-400 hover:text-gray-600">Deselect All</button>
                    </div>
                </div>

                <div class="max-h-96 overflow-y-auto space-y-1">
                    @foreach($users as $user)
                        @php $hasIt = in_array($user->id, $alreadyAwarded); @endphp
                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 {{ $hasIt ? 'opacity-50' : '' }}">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox w-4 h-4 rounded border-gray-300 text-emerald-600" {{ $hasIt ? 'disabled checked' : '' }}>
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                <span class="text-xs text-gray-400 ml-2">{{ $user->username }}</span>
                            </div>
                            @if($hasIt)
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Already has</span>
                            @endif
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="w-full py-3 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">
                    🏅 Award Badge to Selected Users
                </button>
            </div>
        </form>
    </div>

    <!-- Award All Modal -->
    <div id="award-all-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">🏅</span>
                <h3 class="text-lg font-extrabold text-gray-900">Award to ALL Users?</h3>
                <p class="text-sm text-gray-500 mt-1">"{{ $badge->name }}" will be given to every user who doesn't have it yet.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('award-all-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200">Cancel</button>
                <form method="POST" action="{{ route('admin.badges.award.all', $badge) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Award All</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectAll() { document.querySelectorAll('.user-checkbox:not(:disabled)').forEach(c => c.checked = true); }
        function deselectAll() { document.querySelectorAll('.user-checkbox:not(:disabled)').forEach(c => c.checked = false); }
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.admin>
