<x-layouts.organizer>
    <div class="max-w-3xl space-y-6">
        <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Profile Settings</h1>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 font-medium">✅ {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200">
                <ul class="text-sm text-red-700 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organizer.profile.update') }}" enctype="multipart/form-data" id="profile-form" class="space-y-6">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">Basic Info</h2>
                <div class="flex items-center gap-4 mb-4">
                    @if($user->avatar_path)
                        <img src="{{ $user->avatar_path }}" class="w-16 h-16 rounded-2xl object-cover" alt="">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-cyan-400 flex items-center justify-center text-white text-xl font-extrabold">{{ $user->initials() }}</div>
                    @endif
                    <div>
                        <input type="file" name="avatar" accept="image/*" data-preview="avatar-preview" class="block text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                        <img id="avatar-preview" class="hidden mt-2 w-16 h-16 rounded-2xl object-cover" alt="">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name</label>
                        <input type="text" name="organization_name" value="{{ old('organization_name', $user->organization_name) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="organizer_type" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="solo" {{ old('organizer_type', $user->organizer_type) === 'solo' ? 'selected' : '' }}>Solo Guide</option>
                            <option value="agency" {{ old('organizer_type', $user->organizer_type) === 'agency' ? 'selected' : '' }}>Travel Agency</option>
                            <option value="organization" {{ old('organizer_type', $user->organizer_type) === 'organization' ? 'selected' : '' }}>Community / Club</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea name="organizer_bio" rows="3" maxlength="500" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('organizer_bio', $user->organizer_bio) }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">Specialties</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @php
                        $opts = ['mountain'=>'⛰️ Mountains','beach'=>'🏖️ Beaches','island'=>'🏝️ Islands','falls'=>'💧 Waterfalls','river'=>'🌊 Rivers','lake'=>'🏞️ Lakes','campsite'=>'⛺ Camping','historical'=>'🏛️ Historical','food_destination'=>'🍜 Food Trips','road_trip'=>'🚗 Road Trips','hidden_gem'=>'💎 Hidden Gems','city_tour'=>'🏙️ City Tours'];
                        $current = old('specialties', $user->specialties ?? []);
                    @endphp
                    @foreach($opts as $k => $label)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="specialties[]" value="{{ $k }}" class="peer sr-only" {{ in_array($k, $current) ? 'checked' : '' }}>
                            <div class="flex items-center gap-2 p-2.5 rounded-xl border-2 border-gray-100 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all text-sm font-medium text-gray-700">{{ $label }}</div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-3">
                <h2 class="font-bold text-gray-900">Social Links</h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="text-lg w-8 text-center">📘</span>
                        <input type="url" name="social_facebook" value="{{ old('social_facebook', $user->social_links['facebook'] ?? '') }}" placeholder="https://facebook.com/..." class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-lg w-8 text-center">📸</span>
                        <input type="text" name="social_instagram" value="{{ old('social_instagram', $user->social_links['instagram'] ?? '') }}" placeholder="@handle" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-lg w-8 text-center">🌐</span>
                        <input type="url" name="social_website" value="{{ old('social_website', $user->social_links['website'] ?? '') }}" placeholder="https://..." class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>
            </div>

            <button type="button" onclick="showModal('save-modal')" class="px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-md shadow-emerald-500/20 transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #059669, #0891b2);">Save Changes</button>
        </form>
    </div>

    <!-- Save Confirmation Modal -->
    <div id="save-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <span class="text-4xl block mb-3">💾</span>
                <h3 class="text-lg font-extrabold text-gray-900">Save Changes?</h3>
                <p class="text-sm text-gray-500 mt-1">Your profile will be updated.</p>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideModal('save-modal')" class="flex-1 py-2.5 rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-colors">Cancel</button>
                <button type="button" onclick="hideModal('save-modal'); document.getElementById('profile-form').submit();" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white shadow-md transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #059669, #0891b2);">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        function showModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function hideModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    </script>
</x-layouts.organizer>
