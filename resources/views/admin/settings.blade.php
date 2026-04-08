<x-layouts.admin>
    <div class="max-w-3xl space-y-6">
        <h1 class="text-xl font-bold text-gray-900">App Settings</h1>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 font-medium">✅ {{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @foreach($settings->groupBy('group') as $group => $items)
                <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
                    <h2 class="font-bold text-gray-900 uppercase text-xs tracking-wider">{{ $group }}</h2>
                    @foreach($items as $setting)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $setting->description ?? $setting->key }}</label>
                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">
                            <div class="text-[10px] text-gray-400 mt-0.5">Key: {{ $setting->key }}</div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-md" style="background: linear-gradient(135deg, #059669, #0891b2);">Save Settings</button>
        </form>
    </div>
</x-layouts.admin>
