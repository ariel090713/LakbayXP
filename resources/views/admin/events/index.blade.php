<x-layouts.admin>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Event Review</h1>
            @if($counts['pending_review'] > 0)
                <span class="px-3 py-1 rounded-full text-xs font-bold text-white" style="background: linear-gradient(135deg, #f59e0b, #f97316);">{{ $counts['pending_review'] }} pending</span>
            @endif
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 font-medium">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <!-- Status Tabs -->
        <div class="flex gap-2">
            @php
                $tabs = [
                    'pending_review' => ['label' => 'Pending Review', 'count' => $counts['pending_review']],
                    'published' => ['label' => 'Published', 'count' => $counts['published']],
                    'all' => ['label' => 'All Events', 'count' => $counts['all']],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
                <a href="{{ route('admin.events.index', ['status' => $key]) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ $status === $key ? 'text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                   @if($status === $key) style="background: linear-gradient(135deg, #059669, #0891b2);" @endif>
                    {{ $tab['label'] }} ({{ $tab['count'] }})
                </a>
            @endforeach
        </div>

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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Organizer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Slots</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($event->cover_image_path)
                                        <img src="{{ $event->cover_image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover shrink-0" />
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-100 to-cyan-100 flex items-center justify-center text-base shrink-0">📅</div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.events.show', $event) }}" class="font-semibold text-sm text-gray-900 hover:text-emerald-700 transition-colors">{{ $event->title }}</a>
                                        <div class="text-xs text-gray-400">₱{{ number_format($event->fee, 0) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-xs font-bold">
                                        {{ substr($event->organizer->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-900">{{ $event->organizer->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-gray-400">{{ $event->organizer->organization_name ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $event->event_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusStyles[$event->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ str_replace('_', ' ', ucfirst($event->status->value)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $event->max_slots }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.events.show', $event) }}" class="px-3 py-1.5 rounded-lg bg-gray-100 text-xs font-medium text-gray-700 hover:bg-gray-200 transition-colors">View</a>
                                    @if($event->status === \App\Enums\EventStatus::PendingReview)
                                        <button type="button" onclick="showModal('approve-modal-{{ $event->id }}')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white shadow-sm transition-all hover:shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve</button>
                                        <button type="button" onclick="showModal('reject-modal-{{ $event->id }}')" class="px-3 py-1.5 rounded-lg bg-red-50 text-xs font-bold text-red-600 hover:bg-red-100 transition-colors">Reject</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <span class="text-3xl block mb-2">📅</span>
                                <p class="text-sm text-gray-400">No events found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $events->links() }}
    </div>

    <!-- Modals -->
    @foreach($events as $event)
        @if($event->status === \App\Enums\EventStatus::PendingReview)
            <!-- Approve Modal -->
            <div id="approve-modal-{{ $event->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
                    <div class="text-center">
                        <span class="text-4xl block mb-3">✅</span>
                        <h3 class="text-lg font-extrabold text-gray-900">Approve Event?</h3>
                        <p class="text-sm text-gray-500 mt-1">"{{ $event->title }}" will be published and visible to users.</p>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="hideModal('approve-modal-{{ $event->id }}')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                        <form method="POST" action="{{ route('admin.events.approve', $event) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div id="reject-modal-{{ $event->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
                    <div class="text-center">
                        <span class="text-4xl block mb-3">🔙</span>
                        <h3 class="text-lg font-extrabold text-gray-900">Reject Event?</h3>
                        <p class="text-sm text-gray-500 mt-1">"{{ $event->title }}" will be returned to draft status.</p>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="hideModal('reject-modal-{{ $event->id }}')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                        <form method="POST" action="{{ route('admin.events.reject', $event) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-red-500 text-sm font-bold text-white shadow-md hover:bg-red-600">Reject</button>
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
</x-layouts.admin>
