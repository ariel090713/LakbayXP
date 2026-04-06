<x-layouts.organizer>
    <div class="space-y-8">

        <!-- Verification Banner -->
        @unless($user->is_verified_organizer)
            <div class="relative overflow-hidden rounded-2xl p-6" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);">
                <div class="absolute -right-6 -top-6 text-[100px] opacity-10 leading-none">⏳</div>
                <div class="relative text-white">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        <span class="text-sm font-semibold opacity-90">Verification In Progress</span>
                    </div>
                    <h3 class="text-xl font-extrabold">Your account is being reviewed</h3>
                    <p class="text-sm opacity-80 mt-1 max-w-lg">Our team is verifying your organizer profile. Once approved, you'll get a verified badge and can start publishing events.</p>
                    <div class="flex items-center gap-4 mt-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Account created</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Email verified</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium opacity-70"><span class="w-3 h-3 rounded-full border-2 border-white animate-pulse"></span> Admin review</span>
                    </div>
                </div>
            </div>
        @endunless

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 font-medium">✅ {{ session('success') }}</div>
        @endif

        <!-- Welcome Hero -->
        <div class="relative overflow-hidden rounded-2xl p-8" style="background: linear-gradient(135deg, #059669 0%, #0891b2 50%, #6366f1 100%);">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-4 right-8 text-7xl">⛰️</div>
                <div class="absolute bottom-4 right-32 text-5xl">🏝️</div>
            </div>
            <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if($user->avatar_path)
                        <img src="{{ $user->avatar_path }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white/30 shadow-lg" alt="">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-white text-2xl font-extrabold shadow-lg">
                            {{ $user->initials() }}
                        </div>
                    @endif
                    <div class="text-white">
                        <h1 class="text-2xl font-extrabold tracking-tight flex items-center gap-2">
                            {{ $user->organization_name ?? $user->name }}
                            @if($user->is_verified_organizer)
                                <svg class="w-6 h-6 text-emerald-200" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            @endif
                        </h1>
                        <p class="text-sm text-white/70 mt-0.5">{{ ucfirst($user->organizer_type ?? 'Organizer') }} @if($user->specialties) · {{ count($user->specialties) }} specialties @endif</p>
                    </div>
                </div>
                @if($user->is_verified_organizer)
                    <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-emerald-700 bg-white rounded-xl hover:bg-emerald-50 transition-all shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Event
                    </a>
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            @php
                $stats = [
                    ['label' => 'Total Events', 'value' => $totalEvents, 'icon' => '📅', 'gradient' => 'from-emerald-500 to-emerald-600'],
                    ['label' => 'Published', 'value' => $publishedEvents, 'icon' => '🟢', 'gradient' => 'from-cyan-500 to-cyan-600'],
                    ['label' => 'Pending Review', 'value' => $pendingReviewEvents, 'icon' => '📤', 'gradient' => 'from-amber-500 to-amber-600'],
                    ['label' => 'Completed', 'value' => $completedEvents, 'icon' => '🏆', 'gradient' => 'from-indigo-500 to-indigo-600'],
                    ['label' => 'Confirmed', 'value' => $approvedBookings, 'icon' => '🎫', 'gradient' => 'from-teal-500 to-teal-600'],
                    ['label' => 'Pending', 'value' => $pendingBookings, 'icon' => '⏳', 'gradient' => 'from-orange-500 to-orange-600'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $stat['value'] }}</div>
                            <div class="text-[10px] font-semibold text-gray-400 mt-1 uppercase tracking-wider">{{ $stat['label'] }}</div>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $stat['gradient'] }} flex items-center justify-center text-lg shadow-md">
                            {{ $stat['icon'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Getting Started -->
        @if($totalEvents === 0 && $user->is_verified_organizer)
            <div class="relative overflow-hidden rounded-2xl p-8" style="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 50%, #eef2ff 100%);">
                <div class="text-center max-w-md mx-auto">
                    <span class="text-6xl block mb-4">🚀</span>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-2">Create your first adventure</h3>
                    <p class="text-sm text-gray-500 mb-6 leading-relaxed">Set up an event, add your itinerary, publish it, and start accepting bookings from explorers across the Philippines.</p>
                    <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center gap-2 px-8 py-3.5 text-sm font-bold text-white rounded-full shadow-lg shadow-emerald-500/25 hover:shadow-xl transition-all" style="background: linear-gradient(135deg, #059669, #0891b2);">
                        Create Event
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        @endif

        <!-- Upcoming Events Timeline -->
        @if($upcomingEvents->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-extrabold text-gray-900 tracking-tight">Upcoming Events</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-emerald-700 bg-emerald-50">{{ $upcomingEvents->count() }}</span>
                    </div>
                    <a href="{{ route('organizer.events.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 uppercase tracking-wider">View all →</a>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-[18px] top-3 bottom-3 w-0.5 bg-gradient-to-b from-emerald-300 via-cyan-300 to-indigo-300 rounded-full"></div>

                        <div class="space-y-5">
                            @foreach($upcomingEvents as $ue)
                                @php
                                    $daysUntil = now()->startOfDay()->diffInDays($ue->event_date, false);
                                    $urgency = $daysUntil <= 1 ? 'border-red-300 bg-red-50' : ($daysUntil <= 3 ? 'border-amber-300 bg-amber-50' : 'border-emerald-300 bg-emerald-50');
                                    $urgencyDot = $daysUntil <= 1 ? 'bg-red-500' : ($daysUntil <= 3 ? 'bg-amber-500' : 'bg-emerald-500');
                                    $urgencyLabel = $daysUntil === 0 ? 'Today' : ($daysUntil === 1 ? 'Tomorrow' : "In {$daysUntil} days");
                                @endphp
                                <a href="{{ route('organizer.events.show', $ue) }}" class="relative flex items-start gap-4 pl-10 group">
                                    <!-- Timeline dot -->
                                    <div class="absolute left-2.5 top-3 w-3 h-3 rounded-full {{ $urgencyDot }} ring-4 ring-white shadow-sm"></div>

                                    <div class="flex-1 p-4 rounded-xl border {{ $urgency }} hover:shadow-md transition-all duration-200 group-hover:-translate-y-0.5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-sm text-gray-900 truncate group-hover:text-emerald-700 transition-colors">{{ $ue->title }}</div>
                                                <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-500">
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        {{ $ue->event_date->format('M d, Y') }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        {{ $ue->availableSlots() }}/{{ $ue->max_slots }} slots
                                                    </span>
                                                    @if($ue->meeting_place)
                                                        <span class="inline-flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                                            {{ Str::limit($ue->meeting_place, 25) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="text-xs font-bold {{ $daysUntil <= 1 ? 'text-red-600' : ($daysUntil <= 3 ? 'text-amber-600' : 'text-emerald-600') }}">{{ $urgencyLabel }}</span>
                                                @if($ue->availableSlots() === 0)
                                                    <div class="text-[10px] font-bold text-red-500 mt-0.5">FULL</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Recent Events -->
            <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                    <h3 class="text-base font-extrabold text-gray-900 tracking-tight">Recent Events</h3>
                    <a href="{{ route('organizer.events.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 uppercase tracking-wider">View all →</a>
                </div>
                @php
                    $icons = ['mountain'=>'⛰️','beach'=>'🏖️','island'=>'🏝️','falls'=>'💧','river'=>'🌊','lake'=>'🏞️','campsite'=>'⛺','historical'=>'🏛️','food_destination'=>'🍜','road_trip'=>'🚗','hidden_gem'=>'💎'];
                    $statusStyles = [
                        'draft' => 'bg-gray-100 text-gray-600',
                        'pending_review' => 'bg-amber-50 text-amber-700',
                        'published' => 'bg-emerald-50 text-emerald-700',
                        'full' => 'bg-blue-50 text-blue-700',
                        'completed' => 'bg-indigo-50 text-indigo-700',
                        'cancelled' => 'bg-red-50 text-red-600',
                    ];
                @endphp
                <div class="divide-y divide-gray-50">
                    @forelse($recentEvents as $event)
                        <a href="{{ route('organizer.events.show', $event) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/50 transition-colors group">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 shadow-sm" style="background: linear-gradient(135deg, #ecfdf5, #f0fdfa);">
                                {{ $icons[$event->category?->value ?? ''] ?? '📍' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm text-gray-900 truncate group-hover:text-emerald-700 transition-colors">{{ $event->title }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $event->event_date->format('M d, Y') }} · {{ $event->availableSlots() }}/{{ $event->max_slots }} slots</div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusStyles[$event->status->value] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ str_replace('_', ' ', ucfirst($event->status->value)) }}
                            </span>
                        </a>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <span class="text-4xl block mb-3">📅</span>
                            <p class="text-sm text-gray-400 font-medium">No events yet</p>
                            @if($user->is_verified_organizer)
                                <a href="{{ route('organizer.events.create') }}" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 mt-2 hover:text-emerald-700">Create one →</a>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pending Bookings -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                    <h3 class="text-base font-extrabold text-gray-900 tracking-tight">Pending Bookings</h3>
                    @if($pendingBookings > 0)
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold text-white shadow-md" style="background: linear-gradient(135deg, #f59e0b, #f97316);">{{ $pendingBookings }}</span>
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($recentBookings as $booking)
                        <div class="px-6 py-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-extrabold shadow-sm" style="background: linear-gradient(135deg, #059669, #0891b2);">
                                    {{ substr($booking->user->name, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate">{{ $booking->user->name }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $booking->event->title }} · {{ $booking->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="showModal('approve-booking-{{ $booking->id }}')" class="flex-1 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition-all hover:shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve</button>
                                <button type="button" onclick="showModal('reject-booking-{{ $booking->id }}')" class="flex-1 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-bold hover:bg-gray-200 transition-colors">Decline</button>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <span class="text-4xl block mb-3">✅</span>
                            <p class="text-sm text-gray-400 font-medium">All caught up!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Confirmation Modals -->
    @foreach($recentBookings as $booking)
        <!-- Approve Modal -->
        <div id="approve-booking-{{ $booking->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
                <div class="text-center">
                    <span class="text-4xl block mb-3">✅</span>
                    <h3 class="text-lg font-extrabold text-gray-900">Approve Booking?</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $booking->user->name }} will be confirmed for "{{ $booking->event->title }}".</p>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideModal('approve-booking-{{ $booking->id }}')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                    <form method="POST" action="{{ route('organizer.bookings.approve', $booking) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Approve</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div id="reject-booking-{{ $booking->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
                <div class="text-center">
                    <span class="text-4xl block mb-3">❌</span>
                    <h3 class="text-lg font-extrabold text-gray-900">Decline Booking?</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $booking->user->name }}'s booking for "{{ $booking->event->title }}" will be rejected.</p>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideModal('reject-booking-{{ $booking->id }}')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                    <form method="POST" action="{{ route('organizer.bookings.reject', $booking) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-red-500 text-sm font-bold text-white shadow-md hover:bg-red-600">Decline</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.organizer>
