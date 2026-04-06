<x-layouts.admin>
    <div class="max-w-3xl space-y-6">
        <flux:heading size="xl">Edit Reward: {{ $reward->name }}</flux:heading>

        @if($errors->any())
            <flux:callout variant="danger">
                <ul class="list-inside list-disc text-sm">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('admin.rewards.update', $reward) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')
            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input name="name" value="{{ old('name', $reward->name) }}" required />
                </flux:field>
                <flux:field>
                    <flux:label>Slug</flux:label>
                    <flux:input name="slug" value="{{ old('slug', $reward->slug) }}" required />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Description</flux:label>
                <flux:textarea name="description" rows="3">{{ old('description', $reward->description) }}</flux:textarea>
            </flux:field>
            <div class="grid gap-6 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Points Cost</flux:label>
                    <flux:input type="number" name="points_cost" value="{{ old('points_cost', $reward->points_cost) }}" min="1" required />
                </flux:field>
                <flux:field>
                    <flux:label>Stock</flux:label>
                    <flux:input type="number" name="stock" value="{{ old('stock', $reward->stock) }}" min="0" required />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Image</flux:label>
                @if($reward->image_path)
                    <div class="mb-2">
                        <img src="{{ Storage::disk()->url($reward->image_path) }}" alt="Current image" class="h-24 w-auto rounded-lg object-cover" />
                        <p class="mt-1 text-xs text-zinc-400">Current image</p>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" data-preview="preview-image"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                <img id="preview-image" class="hidden mt-2 h-24 w-auto rounded-lg object-cover" alt="Preview" />
            </flux:field>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">Update Reward</flux:button>
                <flux:button href="{{ route('admin.rewards.index') }}">Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts.admin>
