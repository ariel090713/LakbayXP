<x-layouts.admin>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Badges</flux:heading>
            <flux:button variant="primary" href="{{ route('admin.badges.create') }}">
                Add Badge
            </flux:button>
        </div>

        @if(session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Slug</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Criteria Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Active</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($badges as $badge)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $badge->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $badge->slug }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:badge size="sm">{{ ucwords(str_replace('_', ' ', $badge->criteria_type)) }}</flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                @if($badge->is_active)
                                    <flux:badge color="green" size="sm">Active</flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">Inactive</flux:badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" href="{{ route('admin.badges.edit', $badge) }}">Edit</flux:button>
                                    <a href="{{ route('admin.badges.award', $badge) }}" class="px-3 py-1 rounded-lg text-xs font-bold text-white" style="background: linear-gradient(135deg, #059669, #0891b2);">🏅 Award</a>
                                    @if($badge->is_active)
                                        <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button size="sm" variant="danger" type="submit">Deactivate</flux:button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No badges found. <a href="{{ route('admin.badges.create') }}" class="text-blue-600 hover:underline dark:text-blue-400">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $badges->links() }}
        </div>
    </div>
</x-layouts.admin>
