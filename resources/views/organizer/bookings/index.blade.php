<x-layouts.organizer>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Bookings</h1>
                <p class="text-sm text-gray-400 mt-1">{{ $event->title }}</p>
            </div>
            <a href="{{ route('organizer.events.show', $event) }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Event</a>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $pending = $event->bookings->where('status.value', 'pending');
                $approved = $event->bookings->where('status.value', 'approved');
                $rejected = $event->bookings->where('status.value', 'rejected');
                $cancelled = $event->bookings->where('status.value', 'cancelled');
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-amber-600">{{ $pending->count() }}</div>
                <div class="text-xs text-gray-500 mt-1">Pending</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-emerald-600">{{ $approved->count() }}</div>
                <div class="text-xs text-gray-500 mt-1">Approved</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-red-500">{{ $rejected->count() }}</div>
                <div class="text-xs text-gray-500 mt-1">Rejected</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 text-center">
                <div class="text-2xl font-extrabold text-gray-900">{{ $event->availableSlots() }}/{{ $event->max_slots }}</div>
                <div class="text-xs text-gray-500 mt-1">Slots Left</div>
            </div>
        </div>

        <!-- Pending Bookings -->
        @if($pending->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-gray-900">⏳ Pending Approval ({{ $pending->count() }})</h2>
                @if($pending->count() > 1)
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('organizer.bookings.approve-all', $event) }}">
                        @csrf
                        <button type="button" onclick="showModal('approve-all-modal')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve All</button>
                    </form>
                </div>
                @endif
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($pending as $booking)
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-sm font-bold">
                            {{ substr($booking->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $booking->user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $booking->user->email }} · {{ $booking->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="showModal('approve-modal-{{ $booking->id }}')" class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve</button>
                        <button type="button" onclick="showModal('reject-modal-{{ $booking->id }}')" class="px-4 py-2 rounded-xl bg-red-50 text-xs font-bold text-red-600 hover:bg-red-100 transition-colors">Reject</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Approved Bookings -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <h2 class="font-bold text-gray-900">✅ Approved ({{ $approved->count() }})</h2>
            @if($approved->count() > 0)
            <div class="divide-y divide-gray-50">
                @foreach($approved as $booking)
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-sm font-bold">
                            {{ substr($booking->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $booking->user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $booking->user->email }}</div>
                        </div>
                    </div>
                    <div class="text-xs text-emerald-600 font-medium">{{ $booking->approved_at?->diffForHumans() ?? 'Approved' }}</div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-4">No approved bookings yet.</p>
            @endif
        </div>

        <!-- Rejected -->
        @if($rejected->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <h2 class="font-bold text-gray-900">❌ Rejected ({{ $rejected->count() }})</h2>
            <div class="divide-y divide-gray-50">
                @foreach($rejected as $booking)
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-sm font-bold">
                            {{ substr($booking->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">{{ $booking->user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $booking->user->email }}</div>
                        </div>
                    </div>
                    <div class="text-xs text-red-400">{{ $booking->rejected_at?->diffForHumans() ?? 'Rejected' }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Cancelled -->
        @if($cancelled->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            <h2 class="font-bold text-gray-900">🚫 Cancelled ({{ $cancelled->count() }})</h2>
            <div class="divide-y divide-gray-50">
                @foreach($cancelled as $booking)
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-sm font-bold">
                            {{ substr($booking->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ $booking->user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $booking->user->email }}</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">Cancelled</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Approve/Reject Modals for each pending booking -->
    @foreach($pending as $booking)
    <div id="approve-modal-{{ $booking->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">✅</span>
                <h3 class="text-lg font-extrabold text-gray-900">Approve Booking?</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $booking->user->name }} will be confirmed for this event.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('approve-modal-{{ $booking->id }}')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <form method="POST" action="{{ route('organizer.bookings.approve', $booking) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve</button>
                </form>
            </div>
        </div>
    </div>

    <div id="reject-modal-{{ $booking->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">❌</span>
                <h3 class="text-lg font-extrabold text-gray-900">Reject Booking?</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $booking->user->name }}'s booking will be rejected.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('reject-modal-{{ $booking->id }}')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <form method="POST" action="{{ route('organizer.bookings.reject', $booking) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-red-500 text-sm font-bold text-white shadow-md hover:bg-red-600">Reject</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Approve All Modal -->
    @if($pending->count() > 1)
    <div id="approve-all-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">✅</span>
                <h3 class="text-lg font-extrabold text-gray-900">Approve All?</h3>
                <p class="text-sm text-gray-500 mt-1">All {{ $pending->count() }} pending bookings will be approved.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('approve-all-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <form method="POST" action="{{ route('organizer.bookings.approve-all', $event) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve All</button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.organizer>
