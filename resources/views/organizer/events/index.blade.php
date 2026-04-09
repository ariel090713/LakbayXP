<x-layouts.organizer>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">My Events</h1>
            <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-xl hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-md shadow-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Event
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @php
            $statusStyles = [
                'draft' => 'bg-gray-100 text-gray-600',
                'pending_review' => 'bg-amber-50 text-amber-700',
                'published' => 'bg-emerald-50 text-emerald-700',
                'full' => 'bg-blue-50 text-blue-700',
                'completed' => 'bg-indigo-50 text-indigo-700',
                'cancelled' => 'bg-red-50 text-red-600',
            ];
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Slots</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fee</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($event->cover_image_path)
                                        <img src="{{ $event->cover_image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover shrink-0" />
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-100 to-cyan-100 flex items-center justify-center text-lg shrink-0">📅</div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-sm text-gray-900 flex items-center gap-2">
                                            {{ $event->title }}
                                            @if($event->pending_bookings_count > 0)
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold text-white bg-amber-500">{{ $event->pending_bookings_count }} pending</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400">{{ $event->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $event->event_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusStyles[$event->status->value] ?? 'bg-gray-100 text-gray-600' }}">{{ str_replace('_', ' ', ucfirst($event->status->value)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $event->availableSlots() }}/{{ $event->max_slots }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">₱{{ number_format($event->fee, 0) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('organizer.events.show', $event) }}" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-medium text-gray-700 hover:bg-gray-200 transition-colors">View</a>
                                    @if($event->status->value === 'draft')
                                        <a href="{{ route('organizer.events.edit', $event) }}" class="px-3 py-1 rounded-lg bg-gray-100 text-xs font-medium text-gray-700 hover:bg-gray-200 transition-colors">Edit</a>
                                        <button type="button" onclick="showModal('publish-modal-{{ $event->id }}')" class="px-3 py-1 rounded-lg bg-emerald-500 text-xs font-medium text-white hover:bg-emerald-600 transition-colors">Submit</button>
                                    @endif
                                    @if($event->status->value === 'pending_review')
                                        <span class="px-3 py-1 rounded-lg bg-amber-50 text-xs font-medium text-amber-700">Pending</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <span class="text-3xl block mb-2">📅</span>
                                <p class="text-sm text-gray-400">No events yet. <a href="{{ route('organizer.events.create') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Create one</a></p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $events->links() }}
    </div>

    <!-- Publish Modals for each draft event -->
    @foreach($events as $event)
        @if($event->status->value === 'draft')
            <div id="publish-modal-{{ $event->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
                    <div class="text-center">
                        <span class="text-4xl block mb-3">📤</span>
                        <h3 class="text-lg font-extrabold text-gray-900">Submit for Review?</h3>
                        <p class="text-sm text-gray-500 mt-1">"{{ $event->title }}" will be sent to admin for approval.</p>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="hideModal('publish-modal-{{ $event->id }}')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                        <form method="POST" action="{{ route('organizer.events.publish', $event) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.organizer>
