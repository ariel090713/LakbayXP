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
                <flux:input type="file" name="image" accept="image/*" />
            </flux:field>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">Update Reward</flux:button>
                <flux:button href="{{ route('admin.rewards.index') }}">Cancel</flux:button>
            </div>
        </form>
    </div>
</x-layouts.admin>
