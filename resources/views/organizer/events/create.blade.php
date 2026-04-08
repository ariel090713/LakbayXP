<x-layouts.organizer>
    <div class="max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Create Event</h1>
            <a href="{{ route('organizer.events.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
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

        <form id="event-form" method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data" class="space-y-6" onsubmit="return false;">
            @csrf

            <!-- Basic Info -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">📋 Basic Information</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Mt. Pulag Sea of Clouds Adventure" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL-friendly)</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" required placeholder="mt-pulag-sea-of-clouds" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the adventure..." class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Schedule & Logistics -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">🖼️ Images</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" onchange="previewImage(this, 'cover-preview')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                    <img id="cover-preview" class="hidden mt-2 h-32 w-auto rounded-xl object-cover" alt="Preview" />
                    <p class="text-xs text-gray-400 mt-1">Main event image. Max 10MB.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gallery Photos</label>
                    <input type="file" name="gallery[]" accept="image/*" multiple onchange="previewGallery(this)" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                    <div id="gallery-preview" class="flex flex-wrap gap-2 mt-2"></div>
                    <p class="text-xs text-gray-400 mt-1">Up to 10 photos. Max 10MB each.</p>
                </div>
            </div>

            <!-- Schedule & Logistics -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">📅 Schedule & Logistics</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="event_date" value="{{ old('event_date') }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-gray-400">(multi-day)</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                        <select name="difficulty" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select...</option>
                            <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Easy (beginner-friendly)</option>
                            <option value="moderate" {{ old('difficulty') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                            <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                            <option value="extreme" {{ old('difficulty') === 'extreme' ? 'selected' : '' }}>Extreme</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Place</label>
                        <input type="text" name="meeting_place" value="{{ old('meeting_place') }}" placeholder="e.g. Baguio City, Session Road" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Time</label>
                        <input type="text" name="meeting_time" value="{{ old('meeting_time') }}" placeholder="e.g. 5:00 AM" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
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
                    <input type="hidden" name="meeting_lat" id="meeting_lat" value="{{ old('meeting_lat') }}">
                    <input type="hidden" name="meeting_lng" id="meeting_lng" value="{{ old('meeting_lng') }}">
                    <div class="text-xs text-gray-400 mt-1" id="meeting-coords">Click the map or search to pin the meeting location</div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fee (₱)</label>
                        <input type="number" name="fee" value="{{ old('fee', 0) }}" min="0" step="0.01" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Slots</label>
                        <input type="number" name="max_slots" value="{{ old('max_slots') }}" min="1" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="auto_approve_bookings" value="1" {{ old('auto_approve_bookings') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
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
                        <p class="text-xs text-gray-400 mt-1">Add places to visit. System places earn XP & points. Custom places don't.</p>
                    </div>
                    <button type="button" onclick="addItineraryRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Stop</button>
                </div>
                <div id="itinerary-container" class="space-y-3">
                    <!-- JS will add rows here -->
                </div>
            </div>

            <!-- Rules & Instructions -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-gray-900">📜 Rules & Instructions</h2>
                        <p class="text-xs text-gray-400 mt-1">Set requirements, inclusions, reminders, and policies for participants.</p>
                    </div>
                    <button type="button" onclick="addRuleRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Rule</button>
                </div>
                <div id="rules-container" class="space-y-3">
                    <!-- JS will add rows here -->
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="showModal('create-modal')" class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-xl hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-md shadow-emerald-500/20">Create Event</button>
                <a href="{{ route('organizer.events.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Create Confirmation Modal -->
    <div id="create-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">📅</span>
                <h3 class="text-lg font-extrabold text-gray-900">Create Event?</h3>
                <p class="text-sm text-gray-500 mt-1">Your event will be saved as a draft. You can edit it before submitting for review.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('create-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <button type="button" onclick="hideModal('create-modal'); submitEventForm();" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Create</button>
            </div>
        </div>
    </div>

    <script>
        const placesJson = @json($places->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'category' => $p->category->value]));

        // Submit form programmatically — bypass onsubmit="return false"
        function submitEventForm() {
            const form = document.getElementById('event-form');
            form.removeAttribute('onsubmit');
            form.submit();
        }

        function addItineraryRow() {
            const container = document.getElementById('itinerary-container');
            const i = container.children.length;
            const placeOptions = placesJson.map(p => `<option value="${p.id}">${p.name}</option>`).join('');

            const row = document.createElement('div');
            row.className = 'p-4 rounded-xl border border-gray-100 bg-gray-50/50 space-y-3 itinerary-row';
            row.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500">Stop #${i + 1}</span>
                    <button type="button" onclick="this.closest('.itinerary-row').remove()" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">System Place <span class="text-gray-400">(earns XP)</span></label>
                        <select name="itinerary_place_ids[]" onchange="toggleCustom(this)" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                            <option value="">— Custom place (no XP) —</option>
                            ${placeOptions}
                        </select>
                    </div>
                    <div class="custom-fields">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Custom Place Name</label>
                        <input type="text" name="itinerary_custom_names[]" placeholder="e.g. Secret Beach Cove" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                </div>
                <div class="custom-fields">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Custom Location</label>
                    <input type="text" name="itinerary_custom_locations[]" placeholder="e.g. Near Coron, Palawan" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Day #</label>
                        <input type="number" name="itinerary_days[]" value="1" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Time</label>
                        <input type="text" name="itinerary_times[]" placeholder="6:00 AM - 12:00 PM" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Activity</label>
                        <input type="text" name="itinerary_activities[]" placeholder="e.g. Summit climb" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <input type="text" name="itinerary_notes[]" placeholder="Optional notes" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                </div>
            `;
            container.appendChild(row);
        }

        function toggleCustom(select) {
            const row = select.closest('.itinerary-row');
            const customFields = row.querySelectorAll('.custom-fields');
            customFields.forEach(f => f.style.display = select.value ? 'none' : 'block');
        }

        function addRuleRow() {
            const container = document.getElementById('rules-container');
            const row = document.createElement('div');
            row.className = 'flex gap-3 items-start rule-row';
            row.innerHTML = `
                <select name="rule_types[]" class="w-40 shrink-0 px-3 py-2 rounded-lg border border-gray-200 text-sm">
                    <option value="requirement">📋 Requirement</option>
                    <option value="inclusion">✅ Inclusion</option>
                    <option value="exclusion">❌ Exclusion</option>
                    <option value="reminder">⚠️ Reminder</option>
                    <option value="policy">📜 Policy</option>
                    <option value="instruction">📝 Instruction</option>
                    <option value="what_to_bring">🎒 What to Bring</option>
                </select>
                <input type="text" name="rule_contents[]" placeholder="e.g. Bring valid ID, medical certificate" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                <button type="button" onclick="this.closest('.rule-row').remove()" class="text-red-400 hover:text-red-600 text-sm mt-2">✕</button>
            `;
            container.appendChild(row);
        }

        // Add one itinerary row and a few default rules by default
        addItineraryRow();
        addRuleRow();

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
        const defaultLat = 12.8797, defaultLng = 121.7740;
        const map = new google.maps.Map(mapDiv, { center: { lat: defaultLat, lng: defaultLng }, zoom: 6, mapTypeControl: false, streetViewControl: false });
        let marker = null;

        // Search box
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

        // Auto-zoom to current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                if (!document.getElementById('meeting_lat').value) {
                    map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                    map.setZoom(14);
                }
            });
        }

        // Load saved pin
        const savedLat = parseFloat(document.getElementById('meeting_lat').value);
        const savedLng = parseFloat(document.getElementById('meeting_lng').value);
        if (savedLat && savedLng) {
            map.setCenter({ lat: savedLat, lng: savedLng });
            map.setZoom(15);
            setPin(savedLat, savedLng);
        }

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

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { preview.src = e.target.result; preview.classList.remove('hidden'); };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewGallery(input) {
            const container = document.getElementById('gallery-preview');
            container.innerHTML = '';
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'h-20 w-20 rounded-lg object-cover border border-gray-200';
                        container.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
</x-layouts.organizer>
