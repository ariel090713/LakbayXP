<x-layouts.admin>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.events.index') }}" class="text-xs font-medium text-gray-400 hover:text-gray-600 transition-colors">← Back to Events</a>
                <h1 class="text-xl font-bold text-gray-900 mt-1">{{ $event->title }}</h1>
            </div>
            <div class="flex items-center gap-2">
                @if($event->status === \App\Enums\EventStatus::PendingReview)
                    <button type="button" onclick="showModal('approve-modal')" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-md transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve & Publish</button>
                    <button type="button" onclick="showModal('reject-modal')" class="px-5 py-2.5 rounded-xl bg-red-50 text-sm font-bold text-red-600 hover:bg-red-100 transition-colors">Reject</button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 font-medium">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Event Details -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-gray-900">📋 Event Details</h2>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusStyles[$event->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ str_replace('_', ' ', ucfirst($event->status->value)) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="p-3 rounded-xl bg-gray-50">
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Date</div>
                            <div class="text-sm font-bold text-gray-900 mt-1">{{ $event->event_date->format('M d, Y') }}</div>
                            @if($event->end_date)
                                <div class="text-xs text-gray-500">to {{ $event->end_date->format('M d, Y') }}</div>
                            @endif
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50">
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Fee</div>
                            <div class="text-sm font-bold text-gray-900 mt-1">₱{{ number_format($event->fee, 0) }}</div>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50">
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Slots</div>
                            <div class="text-sm font-bold text-gray-900 mt-1">{{ $event->availableSlots() }} / {{ $event->max_slots }}</div>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50">
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Difficulty</div>
                            <div class="text-sm font-bold text-gray-900 mt-1">{{ ucfirst($event->difficulty ?? '—') }}</div>
                        </div>
                    </div>

                    @if($event->meeting_place || $event->meeting_time)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if($event->meeting_place)
                                <div class="p-3 rounded-xl bg-gray-50">
                                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Meeting Place</div>
                                    <div class="text-sm text-gray-900 mt-1">{{ $event->meeting_place }}</div>
                                </div>
                            @endif
                            @if($event->meeting_time)
                                <div class="p-3 rounded-xl bg-gray-50">
                                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Meeting Time</div>
                                    <div class="text-sm text-gray-900 mt-1">{{ $event->meeting_time }}</div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($event->description)
                        <div>
                            <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Description</div>
                            <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $event->description }}</div>
                        </div>
                    @endif

                    <div class="flex items-center gap-4 text-xs text-gray-400">
                        <span>Slug: {{ $event->slug }}</span>
                        <span>Auto-approve: {{ $event->auto_approve_bookings ? 'Yes' : 'No' }}</span>
                        <span>Created: {{ $event->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                </div>

                <!-- Itinerary -->
                @if($event->itinerary->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                        <h2 class="font-bold text-gray-900">🗺️ Itinerary ({{ $event->itinerary->count() }} stops)</h2>
                        @foreach($event->itinerary->groupBy('day_number') as $day => $stops)
                            <div class="space-y-2">
                                <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Day {{ $day }}</div>
                                @foreach($stops as $stop)
                                    <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm shrink-0 {{ $stop->isSystemPlace() ? 'bg-emerald-100' : 'bg-amber-100' }}">
                                            @if($stop->isSystemPlace()) ✅ @else 📍 @endif
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold text-gray-900">{{ $stop->display_name }}</div>
                                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                                @if($stop->time_slot)<span>🕐 {{ $stop->time_slot }}</span>@endif
                                                @if($stop->activity)<span>🎯 {{ $stop->activity }}</span>@endif
                                            </div>
                                            @if($stop->notes)<div class="text-xs text-gray-400 mt-1">{{ $stop->notes }}</div>@endif
                                            @if(!$stop->isSystemPlace())
                                                <div class="text-xs text-amber-600 mt-1">⚠️ Custom place — no XP/points awarded</div>
                                                @if($stop->custom_place_location)<div class="text-xs text-gray-400">📍 {{ $stop->custom_place_location }}</div>@endif
                                            @else
                                                <div class="text-xs text-emerald-600 mt-1">✅ System place — XP will be awarded</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Rules & Instructions -->
                @if($event->rules->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                        <h2 class="font-bold text-gray-900">📜 Rules & Instructions ({{ $event->rules->count() }})</h2>
                        @php
                            $ruleIcons = ['requirement'=>'📋','inclusion'=>'✅','exclusion'=>'❌','reminder'=>'⚠️','policy'=>'📜','instruction'=>'📝','what_to_bring'=>'🎒'];
                        @endphp
                        @foreach($event->rules as $rule)
                            <div class="flex items-start gap-2 p-3 rounded-xl bg-gray-50 text-sm">
                                <span class="text-base">{{ $ruleIcons[$rule->rule_type] ?? '•' }}</span>
                                <div>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ str_replace('_', ' ', $rule->rule_type) }}</span>
                                    <div class="text-gray-700 mt-0.5">{{ $rule->content }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Bookings -->
                @if($event->bookings->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                        <h2 class="font-bold text-gray-900">🎫 Bookings ({{ $event->bookings->count() }})</h2>
                        @php
                            $bookingStyles = ['pending'=>'bg-amber-50 text-amber-700','approved'=>'bg-emerald-50 text-emerald-700','rejected'=>'bg-red-50 text-red-600','cancelled'=>'bg-gray-100 text-gray-500'];
                        @endphp
                        <div class="divide-y divide-gray-50">
                            @foreach($event->bookings as $booking)
                                <div class="flex items-center justify-between py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-xs font-bold">{{ substr($booking->user->name, 0, 1) }}</div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $booking->user->email }}</div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $bookingStyles[$booking->status->value] ?? '' }}">{{ ucfirst($booking->status->value) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Organizer Info -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                    <h2 class="font-bold text-gray-900">👤 Organizer</h2>
                    <div class="flex items-center gap-3">
                        @if($event->organizer->avatar_path)
                            <img src="{{ $event->organizer->avatar_path }}" class="w-12 h-12 rounded-xl object-cover" alt="">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-lg font-bold">
                                {{ $event->organizer->initials() }}
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                                {{ $event->organizer->name }}
                                @if($event->organizer->is_verified_organizer)
                                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">{{ $event->organizer->email }}</div>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        @if($event->organizer->organization_name)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Organization</span>
                                <span class="text-gray-900 font-medium">{{ $event->organizer->organization_name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-400">Type</span>
                            <span class="text-gray-900 font-medium">{{ ucfirst($event->organizer->organizer_type ?? '—') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Verified</span>
                            <span class="text-gray-900 font-medium">{{ $event->organizer->is_verified_organizer ? '✅ Yes' : '❌ No' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Events</span>
                            <span class="text-gray-900 font-medium">{{ $event->organizer->organizedEvents()->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                @if($event->status === \App\Enums\EventStatus::PendingReview)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                        <h2 class="font-bold text-gray-900">⚡ Actions</h2>
                        <button type="button" onclick="showModal('approve-modal')" class="w-full py-3 rounded-xl text-sm font-bold text-white shadow-md transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #059669, #0891b2);">
                            ✅ Approve & Publish
                        </button>
                        <button type="button" onclick="showModal('reject-modal')" class="w-full py-3 rounded-xl bg-red-50 text-sm font-bold text-red-600 hover:bg-red-100 transition-colors">
                            🔙 Reject to Draft
                        </button>
                    </div>
                @endif

                <!-- Event Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                    <h2 class="font-bold text-gray-900">📊 Summary</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Itinerary Stops</span>
                            <span class="text-gray-900 font-medium">{{ $event->itinerary->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">System Places</span>
                            <span class="text-gray-900 font-medium">{{ $event->itinerary->filter(fn($s) => $s->isSystemPlace())->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Custom Places</span>
                            <span class="text-gray-900 font-medium">{{ $event->itinerary->filter(fn($s) => !$s->isSystemPlace())->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Rules</span>
                            <span class="text-gray-900 font-medium">{{ $event->rules->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Bookings</span>
                            <span class="text-gray-900 font-medium">{{ $event->bookings->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div id="approve-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">✅</span>
                <h3 class="text-lg font-extrabold text-gray-900">Approve Event?</h3>
                <p class="text-sm text-gray-500 mt-1">"{{ $event->title }}" will be published and visible to all users.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('approve-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <form method="POST" action="{{ route('admin.events.approve', $event) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">🔙</span>
                <h3 class="text-lg font-extrabold text-gray-900">Reject Event?</h3>
                <p class="text-sm text-gray-500 mt-1">"{{ $event->title }}" will be returned to draft. The organizer can edit and resubmit.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('reject-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <form method="POST" action="{{ route('admin.events.reject', $event) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-red-500 text-sm font-bold text-white shadow-md hover:bg-red-600">Reject</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.admin>
