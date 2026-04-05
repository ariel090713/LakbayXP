<x-layouts.admin>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Rewards</flux:heading>
            <div class="flex gap-2">
                <flux:button href="{{ route('admin.rewards.redemptions') }}">Redemptions</flux:button>
                <flux:button variant="primary" href="{{ route('admin.rewards.create') }}">Add Reward</flux:button>
            </div>
        </div>

        @if(session('success'))
            <flux:callout variant="success">{{ session('success') }}</flux:callout>
        @endif

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Points Cost</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Active</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($rewards as $reward)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">{{ $reward->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ number_format($reward->points_cost) }} pts</td>
                            <td class="px-4 py-3 text-sm">{{ $reward->availableStock() }} / {{ $reward->stock }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($reward->is_active)
                                    <flux:badge color="green" size="sm">Active</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">Inactive</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" href="{{ route('admin.rewards.edit', $reward) }}">Edit</flux:button>
                                    @if($reward->is_active)
                                        <form method="POST" action="{{ route('admin.rewards.destroy', $reward) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <flux:button size="sm" variant="danger" type="submit">Deactivate</flux:button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500">No rewards yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $rewards->links() }}
    </div>
</x-layouts.admin>
