<x-layouts.admin>
    <div class="max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Edit Place: {{ $place->name }}</h1>
            <a href="{{ route('admin.places.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Places</a>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.places.update', $place) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">Basic Information</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $place->name) }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $place->slug) }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('description', $place->description) }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->value }}" {{ old('category', $place->category->value) === $cat->value ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $cat->value)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                        <select name="region" id="region-select" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select Region</option>
                            @foreach($regions as $r)
                                <option value="{{ $r->name }}" {{ old('region', $place->region) === $r->name ? 'selected' : '' }} data-region-id="{{ $r->id }}">{{ $r->name }}</option>
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
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                        <input type="number" name="latitude" value="{{ old('latitude', $place->latitude) }}" step="0.0000001" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="number" name="longitude" value="{{ old('longitude', $place->longitude) }}" step="0.0000001" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">XP Reward</label>
                        <input type="number" name="xp_reward" value="{{ old('xp_reward', $place->xp_reward) }}" min="0" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Image</label>
                    @if($place->cover_image_path)
                        <img src="{{ Storage::disk()->url($place->cover_image_path) }}" alt="" class="h-24 w-auto rounded-lg object-cover mb-2" />
                    @endif
                    <input type="file" name="cover_image" accept="image/*" data-preview="preview-cover" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                    <img id="preview-cover" class="hidden mt-2 h-24 w-auto rounded-lg object-cover" alt="Preview" />
                </div>
            </div>

            <!-- Category-Specific Fields -->
            @if(count($categoryFields) > 0)
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                    <h2 class="font-bold text-gray-900">{{ ucwords(str_replace('_', ' ', $place->category->value)) }} Details</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($categoryFields as $field)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                                @if($field['type'] === 'select')
                                    <select name="meta[{{ $field['key'] }}]" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                        <option value="">Select...</option>
                                        @foreach($field['options'] as $optVal => $optLabel)
                                            <option value="{{ $optVal }}" {{ old('meta.' . $field['key'], $place->getMeta($field['key'])) == $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field['type'] === 'textarea')
                                    <textarea name="meta[{{ $field['key'] }}]" rows="2" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('meta.' . $field['key'], $place->getMeta($field['key'])) }}</textarea>
                                @elseif($field['type'] === 'number')
                                    <input type="number" name="meta[{{ $field['key'] }}]" value="{{ old('meta.' . $field['key'], $place->getMeta($field['key'])) }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                                @else
                                    <input type="text" name="meta[{{ $field['key'] }}]" value="{{ old('meta.' . $field['key'], $place->getMeta($field['key'])) }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Custom Meta Fields -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-gray-900">Additional Info</h2>
                    <button type="button" onclick="addMetaRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">+ Add Field</button>
                </div>
                <div id="custom-meta-container" class="space-y-2">
                    @php
                        $predefinedKeys = collect($categoryFields)->pluck('key')->toArray();
                        $customMeta = $place->meta->filter(fn($m) => !in_array($m->meta_key, $predefinedKeys));
                    @endphp
                    @foreach($customMeta as $meta)
                        <div class="flex gap-2 items-center meta-row">
                            <input type="text" name="custom_meta_keys[]" value="{{ $meta->meta_key }}" placeholder="Key" class="w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                            <input type="text" name="custom_meta_values[]" value="{{ $meta->meta_value }}" placeholder="Value" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                            <button type="button" onclick="this.closest('.meta-row').remove()" class="text-red-400 hover:text-red-600 text-sm">✕</button>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400">Add any extra info like parking, contact number, tips, etc.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-cyan-500 rounded-xl hover:from-emerald-600 hover:to-cyan-600 transition-all shadow-md shadow-emerald-500/20">Update Place</button>
                <a href="{{ route('admin.places.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function addMetaRow() {
            const container = document.getElementById('custom-meta-container');
            const row = document.createElement('div');
            row.className = 'flex gap-2 items-center meta-row';
            row.innerHTML = `
                <input type="text" name="custom_meta_keys[]" placeholder="Key (e.g. parking)" class="w-1/3 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                <input type="text" name="custom_meta_values[]" placeholder="Value" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                <button type="button" onclick="this.closest('.meta-row').remove()" class="text-red-400 hover:text-red-600 text-sm">✕</button>
            `;
            container.appendChild(row);
        }

        // Cascading province dropdown
        const provincesData = @json($provinces);
        const regionSelect = document.getElementById('region-select');
        const provinceSelect = document.getElementById('province-select');
        const currentProvince = '{{ old('province', $place->province) }}';

        function updateProvinces() {
            const selectedOption = regionSelect.options[regionSelect.selectedIndex];
            const regionId = selectedOption?.dataset?.regionId;
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            if (regionId && provincesData[regionId]) {
                provincesData[regionId].forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name;
                    opt.textContent = p.name;
                    if (p.name === currentProvince) opt.selected = true;
                    provinceSelect.appendChild(opt);
                });
            }
        }

        regionSelect.addEventListener('change', updateProvinces);
        updateProvinces();
    </script>
</x-layouts.admin>
