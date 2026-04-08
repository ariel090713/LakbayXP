<x-layouts.admin>
    <div class="max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Add Place</h1>
            <a href="{{ route('admin.places.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Places</a>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.places.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Basic Info -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">Basic Information</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('description') }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" id="category-select" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $cat->value)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                        <select name="region" id="region-select" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select Region</option>
                            @foreach($regions as $r)
                                <option value="{{ $r->name }}" data-region-id="{{ $r->id }}" {{ old('region') === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                        <select name="province" id="province-select" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                </div>
                <!-- Location Map Picker -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">📍 Pin Location</label>
                    <div class="flex gap-2 mb-2">
                        <input type="text" id="map-search" placeholder="Search location..." class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                        <button type="button" onclick="goToMyLocation()" class="px-4 py-2 rounded-lg text-sm font-medium text-white" style="background: linear-gradient(135deg, #059669, #0891b2);">📍 My Location</button>
                    </div>
                    <div id="place-map" class="w-full h-64 rounded-xl border border-gray-200 overflow-hidden"></div>
                    <input type="hidden" name="latitude" id="place_lat" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="place_lng" value="{{ old('longitude') }}">
                    <div class="text-xs text-gray-400 mt-1" id="place-coords">Click the map or search to pin the location</div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">XP Reward</label>
                        <input type="number" name="xp_reward" value="{{ old('xp_reward', 0) }}" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Points Reward</label>
                        <input type="number" name="points_reward" value="{{ old('points_reward', 0) }}" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" data-preview="preview-cover" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                    <img id="preview-cover" class="hidden mt-2 h-24 w-auto rounded-lg object-cover" alt="Preview" />
                </div>
            </div>

            <!-- Dynamic Category Fields -->
            <div id="category-fields-container" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4 hidden">
                <h2 class="font-bold text-gray-900" id="category-fields-title">Category Details</h2>
                <div id="category-fields-grid" class="grid gap-4 sm:grid-cols-2"></div>
            </div>

            <!-- Custom Meta -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-gray-900">Additional Info</h2>
                    <button type="button" onclick="addMetaRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Field</button>
                </div>
                <div id="custom-meta-container" class="space-y-2"></div>
                <p class="text-xs text-gray-400">Add any extra info like parking, contact number, tips, etc.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-xl hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-md shadow-emerald-500/20">Create Place</button>
                <a href="{{ route('admin.places.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        // Province cascading
        const provincesData = @json($provinces);
        const regionSelect = document.getElementById('region-select');
        const provinceSelect = document.getElementById('province-select');
        const currentProvince = '{{ old('province') }}';

        function updateProvinces() {
            const opt = regionSelect.options[regionSelect.selectedIndex];
            const rid = opt?.dataset?.regionId;
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            if (rid && provincesData[rid]) {
                provincesData[rid].forEach(p => {
                    const o = document.createElement('option');
                    o.value = p.name;
                    o.textContent = p.name;
                    if (p.name === currentProvince) o.selected = true;
                    provinceSelect.appendChild(o);
                });
            }
        }
        regionSelect.addEventListener('change', updateProvinces);
        updateProvinces();

        // Dynamic category fields
        const allFields = @json($allCategoryFields);
        const categorySelect = document.getElementById('category-select');
        const container = document.getElementById('category-fields-container');
        const grid = document.getElementById('category-fields-grid');
        const title = document.getElementById('category-fields-title');
        const categoryLabels = {mountain:'Mountain',beach:'Beach',island:'Island',falls:'Waterfall',river:'River',lake:'Lake',campsite:'Campsite',historical:'Historical',food_destination:'Food Spot',road_trip:'Road Trip',hidden_gem:'Hidden Gem'};

        function renderCategoryFields() {
            const cat = categorySelect.value;
            const fields = allFields[cat] || [];
            if (!fields.length) { container.classList.add('hidden'); return; }
            container.classList.remove('hidden');
            title.textContent = (categoryLabels[cat] || cat) + ' Details';
            grid.innerHTML = '';
            fields.forEach(f => {
                const div = document.createElement('div');
                let input = '';
                if (f.type === 'select') {
                    const opts = Object.entries(f.options || {}).map(([v,l]) => `<option value="${v}">${l}</option>`).join('');
                    input = `<select name="meta[${f.key}]" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"><option value="">Select...</option>${opts}</select>`;
                } else if (f.type === 'textarea') {
                    input = `<textarea name="meta[${f.key}]" rows="2" placeholder="${f.placeholder||''}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>`;
                } else if (f.type === 'number') {
                    input = `<input type="number" name="meta[${f.key}]" placeholder="${f.placeholder||''}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />`;
                } else {
                    input = `<input type="text" name="meta[${f.key}]" placeholder="${f.placeholder||''}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />`;
                }
                div.innerHTML = `<label class="block text-sm font-medium text-gray-700 mb-1">${f.label}</label>${input}`;
                grid.appendChild(div);
            });
        }
        categorySelect.addEventListener('change', renderCategoryFields);
        renderCategoryFields();

        // Custom meta
        function addMetaRow() {
            const c = document.getElementById('custom-meta-container');
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-center meta-row';
            row.innerHTML = `
                <input type="text" name="custom_meta_keys[]" placeholder="Key" class="w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                <input type="text" name="custom_meta_values[]" placeholder="Value" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                <button type="button" onclick="this.closest('.meta-row').remove()" class="text-red-400 hover:text-red-600 text-sm">✕</button>
            `;
            c.appendChild(row);
        }
    </script>
    <script>
        // Place location map
        const mapDiv = document.getElementById('place-map');
        if (mapDiv && typeof google !== 'undefined') {
            const map = new google.maps.Map(mapDiv, { center: { lat: 12.8797, lng: 121.7740 }, zoom: 6, mapTypeControl: false, streetViewControl: false });
            let marker = null;

            const searchInput = document.getElementById('map-search');
            const searchBox = new google.maps.places.SearchBox(searchInput);
            searchBox.addListener('places_changed', function() {
                const places = searchBox.getPlaces();
                if (!places.length) return;
                const place = places[0];
                if (!place.geometry) return;
                map.setCenter(place.geometry.location);
                map.setZoom(16);
                setPin(place.geometry.location.lat(), place.geometry.location.lng());
            });

            if (navigator.geolocation && !document.getElementById('place_lat').value) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                    map.setZoom(14);
                });
            }

            const savedLat = parseFloat(document.getElementById('place_lat').value);
            const savedLng = parseFloat(document.getElementById('place_lng').value);
            if (savedLat && savedLng) {
                map.setCenter({ lat: savedLat, lng: savedLng });
                map.setZoom(15);
                setPin(savedLat, savedLng);
            }

            function setPin(lat, lng) {
                document.getElementById('place_lat').value = lat.toFixed(7);
                document.getElementById('place_lng').value = lng.toFixed(7);
                document.getElementById('place-coords').textContent = '📍 ' + lat.toFixed(4) + ', ' + lng.toFixed(4);
                if (marker) marker.setMap(null);
                marker = new google.maps.Marker({ position: { lat, lng }, map, draggable: true });
                marker.addListener('dragend', function() { setPin(marker.getPosition().lat(), marker.getPosition().lng()); });
            }

            map.addListener('click', function(e) { setPin(e.latLng.lat(), e.latLng.lng()); });

            window.goToMyLocation = function() {
                if (!navigator.geolocation) return alert('Geolocation not supported');
                navigator.geolocation.getCurrentPosition(function(pos) {
                    map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                    map.setZoom(16);
                    setPin(pos.coords.latitude, pos.coords.longitude);
                });
            };
        }
    </script>
</x-layouts.admin>
