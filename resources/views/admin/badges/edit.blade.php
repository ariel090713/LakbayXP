<x-layouts.admin>
    <div class="max-w-3xl space-y-6">
        <flux:heading size="xl">Edit Badge: {{ $badge->name }}</flux:heading>

        @if($errors->any())
            <flux:callout variant="danger">
                <p>Please fix the following errors:</p>
                <ul class="mt-1 list-inside list-disc text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('admin.badges.update', $badge) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input name="name" value="{{ old('name', $badge->name) }}" required />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input name="slug" value="{{ old('slug', $badge->slug) }}" required />
                    <flux:error name="slug" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea name="description" rows="3">{{ old('description', $badge->description) }}</flux:textarea>
                <flux:error name="description" />
            </flux:field>

            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Criteria Type</flux:label>
                    <flux:select name="criteria_type" required>
                        <flux:select.option value="">Select criteria type</flux:select.option>
                        <flux:select.option value="unlock_count" :selected="old('criteria_type', $badge->criteria_type) === 'unlock_count'">Unlock Count</flux:select.option>
                        <flux:select.option value="category_count" :selected="old('criteria_type', $badge->criteria_type) === 'category_count'">Category Count</flux:select.option>
                        <flux:select.option value="region_count" :selected="old('criteria_type', $badge->criteria_type) === 'region_count'">Region Count</flux:select.option>
                        <flux:select.option value="streak" :selected="old('criteria_type', $badge->criteria_type) === 'streak'">Streak</flux:select.option>
                    </flux:select>
                    <flux:error name="criteria_type" />
                </flux:field>

                <flux:field>
                    <flux:label>Category (optional)</flux:label>
                    <flux:input name="category" value="{{ old('category', $badge->category) }}" />
                    <flux:error name="category" />
                </flux:field>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:heading size="lg">Criteria Value</flux:heading>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Define the criteria thresholds for this badge.</p>

                <div class="grid gap-6 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Count</flux:label>
                        <flux:input type="number" name="criteria_value[count]" value="{{ old('criteria_value.count', $badge->criteria_value['count'] ?? '') }}" min="1" />
                        <flux:error name="criteria_value.count" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Category (for category_count)</flux:label>
                        <flux:input name="criteria_value[category]" value="{{ old('criteria_value.category', $badge->criteria_value['category'] ?? '') }}" />
                        <flux:error name="criteria_value.category" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Region (for region_count)</flux:label>
                        <flux:input name="criteria_value[region]" value="{{ old('criteria_value.region', $badge->criteria_value['region'] ?? '') }}" />
                        <flux:error name="criteria_value.region" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Days (for streak)</flux:label>
                        <flux:input type="number" name="criteria_value[days]" value="{{ old('criteria_value.days', $badge->criteria_value['days'] ?? '') }}" min="1" />
                        <flux:error name="criteria_value.days" />
                    </flux:field>
                </div>
            </div>

            <flux:field>
                <flux:label>Icon</flux:label>
                @if($badge->icon_path)
                    <div class="mb-2">
                        <img src="{{ Storage::disk()->url($badge->icon_path) }}" alt="Current icon" class="h-16 w-16 rounded-lg object-cover" />
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Current icon</p>
                    </div>
                @endif
                <input type="file" name="icon" accept="image/*" data-preview="preview-icon"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                <img id="preview-icon" class="hidden mt-2 h-16 w-16 rounded-lg object-cover" alt="Preview" />
                <flux:error name="icon" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <flux:checkbox name="is_active" value="1" checked="{{ old('is_active', $badge->is_active) ? 'checked' : '' }}" />
                    Active
                </flux:label>
            </flux:field>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">Update Badge</flux:button>
                <flux:button href="{{ route('admin.badges.index') }}">Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts.admin>
