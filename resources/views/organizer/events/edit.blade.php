<x-layouts.organizer>
    <div class="max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Edit Event</h1>
            <a href="{{ route('organizer.events.show', $event) }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <!-- Map search outside form to prevent Google Maps SearchBox form injection issues -->
        <div id="map-search-wrapper" class="hidden">
            <input type="text" id="map-search" placeholder="Search location..." class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
        </div>

        <form id="event-form" method="POST" action="{{ route('organizer.events.update', $event) }}" class="space-y-6" onsubmit="return false;">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">📋 Basic Information</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                        <input type="text" name="title" value="{{ old('title', $event->title) }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $event->slug) }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('description', $event->description) }}</textarea>
                </div>
            </div>

            <!-- Schedule -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">📅 Schedule & Logistics</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-gray-400">(multi-day)</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                        <select name="difficulty" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select...</option>
                            <option value="easy" {{ old('difficulty', $event->difficulty) === 'easy' ? 'selected' : '' }}>Easy (beginner-friendly)</option>
                            <option value="moderate" {{ old('difficulty', $event->difficulty) === 'moderate' ? 'selected' : '' }}>Moderate</option>
                            <option value="hard" {{ old('difficulty', $event->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                            <option value="extreme" {{ old('difficulty', $event->difficulty) === 'extreme' ? 'selected' : '' }}>Extreme</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Place</label>
                        <input type="text" name="meeting_place" value="{{ old('meeting_place', $event->meeting_place) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Time</label>
                        <input type="text" name="meeting_time" value="{{ old('meeting_time', $event->meeting_time) }}" placeholder="e.g. 5:00 AM" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <!-- Meeting Point Map -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">📍 Pin Meeting Location</label>
                    <div class="flex gap-2 mb-2">
                        <div id="map-search-slot" class="flex-1"></div>
                        <button type="button" onclick="goToMyLocation()" class="px-4 py-2 rounded-lg text-sm font-medium text-white" style="background: linear-gradient(135deg, #059669, #0891b2);">📍 My Location</button>
                    </div>
                    <div id="meeting-map" class="w-full h-64 rounded-xl border border-gray-200 overflow-hidden"></div>
                    <input type="hidden" name="meeting_lat" id="meeting_lat" value="{{ old('meeting_lat', $event->meeting_lat) }}">
                    <input type="hidden" name="meeting_lng" id="meeting_lng" value="{{ old('meeting_lng', $event->meeting_lng) }}">
                    <div class="text-xs text-gray-400 mt-1" id="meeting-coords">
                        @if($event->meeting_lat)📍 {{ number_format($event->meeting_lat, 4) }}, {{ number_format($event->meeting_lng, 4) }}@else Click the map or search to pin the meeting location @endif
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fee (₱)</label>
                        <input type="number" name="fee" value="{{ old('fee', $event->fee) }}" min="0" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Slots</label>
                        <input type="number" name="max_slots" value="{{ old('max_slots', $event->max_slots) }}" min="1" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="auto_approve_bookings" value="1" {{ old('auto_approve_bookings', $event->auto_approve_bookings) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                            <span class="text-sm text-gray-700">Auto-approve bookings</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Itinerary -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-gray-900">🗺️ Itinerary</h2>
                        <p class="text-xs text-gray-400 mt-1">System places earn XP & points. Custom places don't.</p>
                    </div>
                    <button type="button" onclick="addItineraryRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Stop</button>
                </div>
                <div id="itinerary-container" class="space-y-3"></div>
            </div>

            <!-- Rules & Instructions -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-gray-900">📜 Rules & Instructions</h2>
                        <p class="text-xs text-gray-400 mt-1">Requirements, inclusions, reminders, policies.</p>
                    </div>
                    <button type="button" onclick="addRuleRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Rule</button>
                </div>
                <div id="rules-container" class="space-y-3"></div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="showModal('update-modal')" class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-xl hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-md shadow-emerald-500/20">Update Event</button>
                <a href="{{ route('organizer.events.show', $event) }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Update Confirmation Modal -->
    <div id="update-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">💾</span>
                <h3 class="text-lg font-extrabold text-gray-900">Update Event?</h3>
                <p class="text-sm text-gray-500 mt-1">Your changes will be saved.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('update-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <button type="button" onclick="hideModal('update-modal'); submitEventForm();" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Update</button>
            </div>
        </div>
    </div>

    <script>
        const placesJson = @json($places->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; }));
        const existingItinerary = @json($itineraryData);
        const existingRules = @json($rulesData);

        // Submit form programmatically — bypass onsubmit="return false"
        function submitEventForm() {
            const form = document.getElementById('event-form');
            form.removeAttribute('onsubmit');
            form.submit();
        }

        function addItineraryRow(data = {}) {
            const container = document.getElementById('itinerary-container');
            const i = container.children.length;
            const opts = placesJson.map(p => `<option value="${p.id}" ${p.id == (data.place_id||'') ? 'selected' : ''}>${p.name}</option>`).join('');
            const isCustom = !data.place_id;

            const row = document.createElement('div');
            row.className = 'p-4 rounded-xl border border-gray-100 bg-gray-50/50 space-y-3 itinerary-row';
            row.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500">Stop #${i+1}</span>
                    <button type="button" onclick="this.closest('.itinerary-row').remove()" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">System Place</label>
                        <select name="itinerary_place_ids[]" onchange="toggleCustom(this)" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                            <option value="">— Custom place —</option>${opts}
                        </select>
                    </div>
                    <div class="custom-fields" style="display:${isCustom?'block':'none'}">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Custom Name</label>
                        <input type="text" name="itinerary_custom_names[]" value="${data.custom_name||''}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                </div>
                <div class="custom-fields" style="display:${isCustom?'block':'none'}">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Custom Location</label>
                    <input type="text" name="itinerary_custom_locations[]" value="${data.custom_location||''}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Day #</label>
                        <input type="number" name="itinerary_days[]" value="${data.day||1}" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Time</label>
                        <input type="text" name="itinerary_times[]" value="${data.time||''}" placeholder="6:00 AM - 12:00 PM" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Activity</label>
                        <input type="text" name="itinerary_activities[]" value="${data.activity||''}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <input type="text" name="itinerary_notes[]" value="${data.notes||''}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                </div>
            `;
            container.appendChild(row);
        }

        function toggleCustom(sel) {
            const row = sel.closest('.itinerary-row');
            row.querySelectorAll('.custom-fields').forEach(f => f.style.display = sel.value ? 'none' : 'block');
        }

        function addRuleRow(data = {}) {
            const container = document.getElementById('rules-container');
            const types = ['requirement','inclusion','exclusion','reminder','policy','instruction','what_to_bring'];
            const labels = {'requirement':'📋 Requirement','inclusion':'✅ Inclusion','exclusion':'❌ Exclusion','reminder':'⚠️ Reminder','policy':'📜 Policy','instruction':'📝 Instruction','what_to_bring':'🎒 What to Bring'};
            const opts = types.map(t => `<option value="${t}" ${t===(data.type||'requirement')?'selected':''}>${labels[t]}</option>`).join('');

            const row = document.createElement('div');
            row.className = 'flex gap-3 items-start rule-row';
            row.innerHTML = `
                <select name="rule_types[]" class="w-40 shrink-0 px-3 py-2 rounded-lg border border-gray-200 text-sm">${opts}</select>
                <input type="text" name="rule_contents[]" value="${data.content||''}" placeholder="e.g. Bring valid ID" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                <button type="button" onclick="this.closest('.rule-row').remove()" class="text-red-400 hover:text-red-600 text-sm mt-2">✕</button>
            `;
            container.appendChild(row);
        }

        // Load existing data
        if (existingItinerary.length) {
            existingItinerary.forEach(s => addItineraryRow(s));
        } else {
            addItineraryRow();
        }
        if (existingRules.length) {
            existingRules.forEach(r => addRuleRow(r));
        } else {
            addRuleRow();
        }

        // Move search input outside the form to prevent Google Maps SearchBox form injection
        const searchInput = document.getElementById('map-search');
        const searchSlot = document.getElementById('map-search-slot');
        const wrapper = document.getElementById('map-search-wrapper');
        searchSlot.appendChild(searchInput);
        searchInput.style.width = '100%';
        wrapper.remove();

        // Prevent Enter key on any input from submitting the form
        document.getElementById('event-form').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
            }
        });

        // Meeting point map — Google Maps
        const mapDiv = document.getElementById('meeting-map');
        const initLat = {{ $event->meeting_lat ?? 12.8797 }};
        const initLng = {{ $event->meeting_lng ?? 121.7740 }};
        const initZoom = {{ $event->meeting_lat ? 15 : 6 }};
        const map = new google.maps.Map(mapDiv, { center: { lat: initLat, lng: initLng }, zoom: initZoom, mapTypeControl: false, streetViewControl: false });
        let marker = null;

        const searchBox = new google.maps.places.SearchBox(searchInput);
        searchBox.addListener('places_changed', function() {
            const places = searchBox.getPlaces();
            if (!places.length) return;
            const place = places[0];
            if (!place.geometry) return;
            const lat = place.geometry.location.lat(), lng = place.geometry.location.lng();
            map.setCenter({ lat, lng });
            map.setZoom(16);
            setPin(lat, lng);
        });

        @if(!$event->meeting_lat)
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                map.setZoom(14);
            });
        }
        @endif

        @if($event->meeting_lat)
        setPin({{ $event->meeting_lat }}, {{ $event->meeting_lng }});
        @endif

        function setPin(lat, lng) {
            document.getElementById('meeting_lat').value = lat.toFixed(7);
            document.getElementById('meeting_lng').value = lng.toFixed(7);
            document.getElementById('meeting-coords').textContent = `📍 ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
            if (marker) marker.setMap(null);
            marker = new google.maps.Marker({ position: { lat, lng }, map, draggable: true });
            marker.addListener('dragend', function() {
                const pos = marker.getPosition();
                setPin(pos.lat(), pos.lng());
            });
        }

        map.addListener('click', function(e) { setPin(e.latLng.lat(), e.latLng.lng()); });

        window.goToMyLocation = function() {
            if (!navigator.geolocation) return alert('Geolocation not supported');
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                map.setCenter({ lat, lng });
                map.setZoom(16);
                setPin(lat, lng);
            });
        };

        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.organizer>
