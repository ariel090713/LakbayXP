<x-layouts.admin>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">XP Management</h1>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 font-medium">✅ {{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Users List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                        <h2 class="font-bold text-gray-900">Explorers</h2>
                        <form method="GET" class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, username, email..." class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm w-64">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-gray-100 text-sm font-medium">Search</button>
                        </form>
                    </div>
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Level</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">XP</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Stats</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Grant XP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-xs font-bold">{{ $user->initials() }}</div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-400">{{ $user->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-bold gradient-text">Lvl {{ $user->level }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($user->xp) }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-400">🔓{{ $user->unlocked_places_count }} 🏅{{ $user->badges_count }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" onclick="openGrant({{ $user->id }}, '{{ addslashes($user->name) }}')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white shadow-sm" style="background: linear-gradient(135deg, #059669, #0891b2);">⚡ Grant</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-4 py-3">{{ $users->links() }}</div>
                </div>
            </div>

            <!-- Recent Grants Sidebar -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">Recent Grants</h2>
                    @forelse($recentGrants as $grant)
                        <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs">⚡</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900">+{{ $grant->amount }} XP → {{ $grant->user?->name }}</div>
                                <div class="text-xs text-gray-400 truncate">{{ $grant->description }}</div>
                                <div class="text-[10px] text-gray-300">{{ $grant->created_at->diffForHumans() }} by {{ $grant->grantedBy?->name ?? 'System' }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No grants yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Grant XP Modal -->
    <div id="grant-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
            <div class="text-center mb-4">
                <span class="text-4xl block mb-2">⚡</span>
                <h3 class="text-lg font-extrabold text-gray-900">Grant XP</h3>
                <p class="text-sm text-gray-500" id="grant-user-label">to User</p>
            </div>
            <form method="POST" action="{{ route('admin.xp.grant') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="user_id" id="grant-user-id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                    <input type="number" name="amount" min="1" max="10000" required placeholder="e.g. 100" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description" required placeholder="e.g. Promo bonus, Event reward" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category (optional)</label>
                    <select name="category" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                        <option value="">— No category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['value'] }}">{{ $cat['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideModal('grant-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Grant XP</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openGrant(userId, userName) {
            document.getElementById('grant-user-id').value = userId;
            document.getElementById('grant-user-label').textContent = 'to ' + userName;
            showModal('grant-modal');
        }
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.admin>
