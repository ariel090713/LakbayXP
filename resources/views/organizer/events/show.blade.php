<x-layouts.organizer>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $event->title }}</h1>
                <p class="text-sm text-gray-400 mt-1">{{ $event->slug }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($event->status->value === 'draft')
                    <a href="{{ route('organizer.events.edit', $event) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Edit</a>
                    @if(auth()->user()->is_verified_organizer)
                        <button type="button" onclick="showModal('publish-modal')" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-500 rounded-xl hover:bg-emerald-600 transition-colors">Submit for Review</button>
                    @else
                        <span class="px-4 py-2 text-sm font-semibold text-amber-700 bg-amber-50 rounded-xl">⚠️ Account not verified</span>
                    @endif
                @endif
                @if($event->status->value === 'pending_review')
                    <span class="px-4 py-2 text-sm font-semibold text-amber-700 bg-amber-50 rounded-xl">⏳ Awaiting Admin Approval</span>
                @endif
                @if(in_array($event->status->value, ['published', 'full']))
                    @if($event->event_date->isPast() || $event->event_date->isToday())
                        <button type="button" onclick="showModal('complete-modal')" class="px-4 py-2 text-sm font-semibold text-white bg-blue-500 rounded-xl hover:bg-blue-600 transition-colors">Complete</button>
                    @endif
                    <button type="button" onclick="showModal('cancel-modal')" class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Cancel Event</button>
                @endif
                <a href="{{ route('organizer.events.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        @if($event->status->value === 'draft' && !auth()->user()->is_verified_organizer)
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-700">
                ⚠️ Your account is not yet verified. You can create and edit events, but you need admin verification before submitting for review.
            </div>
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
            <!-- Event Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Cover Image -->
                @if($event->cover_image_path)
                    <div class="rounded-2xl overflow-hidden border border-gray-100">
                        <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-full h-64 object-cover" />
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                    <h2 class="font-bold text-gray-900">Event Details</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-gray-500">Status</div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusStyles[$event->status->value] ?? '' }}">{{ str_replace('_', ' ', ucfirst($event->status->value)) }}</span>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Date</div>
                            <div class="text-sm font-medium text-gray-900">{{ $event->event_date->format('M d, Y') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Fee</div>
                            <div class="text-sm font-medium text-gray-900">₱{{ number_format($event->fee, 0) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Slots</div>
                            <div class="text-sm font-medium text-gray-900">{{ $event->availableSlots() }}/{{ $event->max_slots }}</div>
                        </div>
                    </div>
                    @if($event->meeting_place)
                        <div>
                            <div class="text-xs text-gray-500">Meeting Place</div>
                            <div class="text-sm text-gray-900">{{ $event->meeting_place }}</div>
                        </div>
                    @endif
                    @if($event->description)
                        <div>
                            <div class="text-xs text-gray-500">Description</div>
                            <div class="text-sm text-gray-700 leading-relaxed">{{ $event->description }}</div>
                        </div>
                    @endif
                </div>

                <!-- Itinerary -->
                @if($event->itinerary->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                        <h2 class="font-bold text-gray-900">🗺️ Itinerary</h2>
                        @foreach($event->itinerary->groupBy('day_number') as $day => $stops)
                            <div class="space-y-2">
                                <div class="text-xs font-bold text-emerald-600 uppercase">Day {{ $day }}</div>
                                @foreach($stops as $stop)
                                    <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-sm shrink-0">
                                            @if($stop->isSystemPlace()) ✅ @else 📍 @endif
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold text-gray-900">{{ $stop->display_name }}</div>
                                            @if($stop->time_slot)<div class="text-xs text-gray-500">{{ $stop->time_slot }}</div>@endif
                                            @if($stop->activity)<div class="text-xs text-gray-500">{{ $stop->activity }}</div>@endif
                                            @if($stop->notes)<div class="text-xs text-gray-400 mt-1">{{ $stop->notes }}</div>@endif
                                            @if(!$stop->isSystemPlace())<div class="text-xs text-amber-600 mt-1">⚠️ Custom place — no XP/points</div>@endif
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
                        <h2 class="font-bold text-gray-900">📜 Rules & Instructions</h2>
                        @php
                            $ruleIcons = ['requirement'=>'📋','inclusion'=>'✅','exclusion'=>'❌','reminder'=>'⚠️','policy'=>'📜','instruction'=>'📝','what_to_bring'=>'🎒'];
                        @endphp
                        @foreach($event->rules as $rule)
                            <div class="flex items-start gap-2 text-sm">
                                <span>{{ $ruleIcons[$rule->rule_type] ?? '•' }}</span>
                                <div>
                                    <span class="text-xs font-semibold text-gray-500 uppercase">{{ str_replace('_', ' ', $rule->rule_type) }}:</span>
                                    <span class="text-gray-700">{{ $rule->content }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Gallery -->
                @if($event->photos->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                        <h2 class="font-bold text-gray-900">📸 Gallery ({{ $event->photos->count() }})</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($event->photos as $photo)
                                <div class="relative group">
                                    <img src="{{ $photo->photo_url }}" alt="" class="w-full h-32 rounded-xl object-cover border border-gray-100 cursor-pointer" onclick="openLightbox('{{ $photo->photo_url }}')" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Bookings Sidebar -->
            <div class="space-y-6">
                @php
                    $pendingBookings = $event->bookings->where('status.value', 'pending');
                    $approvedBookings = $event->bookings->where('status.value', 'approved');
                    $rejectedBookings = $event->bookings->where('status.value', 'rejected');
                    $cancelledBookings = $event->bookings->where('status.value', 'cancelled');
                @endphp

                <!-- Slots Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 rounded-xl bg-amber-50">
                            <div class="text-xl font-extrabold text-amber-600">{{ $pendingBookings->count() }}</div>
                            <div class="text-[10px] text-gray-500 uppercase font-semibold">Pending</div>
                        </div>
                        <div class="p-3 rounded-xl bg-emerald-50">
                            <div class="text-xl font-extrabold text-emerald-600">{{ $approvedBookings->count() }}</div>
                            <div class="text-[10px] text-gray-500 uppercase font-semibold">Approved</div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-sm text-gray-500">
                        {{ $event->availableSlots() }} / {{ $event->max_slots }} slots left
                    </div>
                </div>

                <!-- Pending Bookings with Approve/Reject -->
                @if($pendingBookings->count() > 0)
                <div class="bg-white rounded-2xl border border-amber-200 p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-gray-900 text-sm">⏳ Pending ({{ $pendingBookings->count() }})</h2>
                        @if($pendingBookings->count() > 1)
                            <button type="button" onclick="showModal('approve-all-modal')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Approve All</button>
                        @endif
                    </div>
                    @foreach($pendingBookings as $booking)
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-400 flex items-center justify-center text-white text-xs font-bold shrink-0">{{ substr($booking->user->name, 0, 1) }}</div>
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate">{{ $booking->user->name }}</div>
                                <div class="text-[10px] text-gray-400">{{ $booking->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <form method="POST" action="{{ route('organizer.bookings.approve', $booking) }}">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center text-sm" title="Approve">✓</button>
                            </form>
                            <form method="POST" action="{{ route('organizer.bookings.reject', $booking) }}">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center text-sm" title="Reject">✕</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Approved Bookings -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                    <h2 class="font-bold text-gray-900 text-sm">✅ Approved ({{ $approvedBookings->count() }})</h2>
                    @forelse($approvedBookings as $booking)
                    <div class="flex items-center justify-between py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-xs font-bold">{{ substr($booking->user->name, 0, 1) }}</div>
                            <span class="text-sm text-gray-900">{{ $booking->user->name }}</span>
                        </div>
                        <span class="text-[10px] text-emerald-500">{{ $booking->approved_at?->diffForHumans() }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-2">No approved bookings.</p>
                    @endforelse
                </div>

                <!-- Rejected / Cancelled -->
                @if($rejectedBookings->count() > 0 || $cancelledBookings->count() > 0)
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                    @if($rejectedBookings->count() > 0)
                    <h2 class="font-bold text-gray-900 text-sm">❌ Rejected ({{ $rejectedBookings->count() }})</h2>
                    @foreach($rejectedBookings as $booking)
                    <div class="flex items-center justify-between py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-bold">{{ substr($booking->user->name, 0, 1) }}</div>
                            <span class="text-sm text-gray-500">{{ $booking->user->name }}</span>
                        </div>
                        <span class="text-[10px] text-red-400">Rejected</span>
                    </div>
                    @endforeach
                    @endif
                    @if($cancelledBookings->count() > 0)
                    <h2 class="font-bold text-gray-900 text-sm mt-3">🚫 Cancelled ({{ $cancelledBookings->count() }})</h2>
                    @foreach($cancelledBookings as $booking)
                    <div class="flex items-center justify-between py-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-bold">{{ substr($booking->user->name, 0, 1) }}</div>
                            <span class="text-sm text-gray-500">{{ $booking->user->name }}</span>
                        </div>
                        <span class="text-[10px] text-gray-400">Cancelled</span>
                    </div>
                    @endforeach
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Publish/Submit for Review Modal -->
    <div id="publish-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">📤</span>
                <h3 class="text-lg font-extrabold text-gray-900">Submit for Review?</h3>
                <p class="text-sm text-gray-500 mt-1">Your event will be sent to admin for approval before it goes live.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('publish-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <form method="POST" action="{{ route('organizer.events.publish', $event) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Event Modal -->
    <div id="cancel-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">⚠️</span>
                <h3 class="text-lg font-extrabold text-gray-900">Cancel Event?</h3>
                <p class="text-sm text-gray-500 mt-1">This action cannot be undone. All bookings will be affected.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('cancel-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Go Back</button>
                <form method="POST" action="{{ route('organizer.events.cancel', $event) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-red-500 text-sm font-bold text-white shadow-md hover:bg-red-600">Cancel Event</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Event Modal -->
    <div id="complete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">🎉</span>
                <h3 class="text-lg font-extrabold text-gray-900">Complete Event?</h3>
                <p class="text-sm text-gray-500 mt-1">All approved attendees will unlock the places and earn XP.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('complete-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <form method="POST" action="{{ route('organizer.events.complete', $event) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">Complete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve All Modal -->
    <div id="approve-all-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">✅</span>
                <h3 class="text-lg font-extrabold text-gray-900">Approve All Pending?</h3>
                <p class="text-sm text-gray-500 mt-1">All pending bookings will be approved.</p>
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

    <!-- Lightbox -->
    <div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm" onclick="closeLightbox()">
        <img id="lightbox-img" src="" alt="" class="max-h-[85vh] max-w-[90vw] rounded-2xl object-contain" />
    </div>

    <script>
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
        function openLightbox(url) { document.getElementById('lightbox-img').src = url; document.getElementById('lightbox').classList.remove('hidden'); document.getElementById('lightbox').classList.add('flex'); }
        function closeLightbox() { document.getElementById('lightbox').classList.add('hidden'); document.getElementById('lightbox').classList.remove('flex'); }
    </script>
</x-layouts.organizer>
