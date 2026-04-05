<x-layouts.app>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">My Events</flux:heading>
            <flux:button variant="primary" href="{{ route('organizer.events.create') }}">
                Create Event
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
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Place</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Slots</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($events as $event)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $event->title }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $event->place->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $event->event_date->format('M d, Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                @switch($event->status->value)
                                    @case('draft')
                                        <flux:badge color="zinc" size="sm">Draft</flux:badge>
                                        @break
                                    @case('published')
                                        <flux:badge color="green" size="sm">Published</flux:badge>
                                        @break
                                    @case('full')
                                        <flux:badge color="amber" size="sm">Full</flux:badge>
                                        @break
                                    @case('completed')
                                        <flux:badge color="blue" size="sm">Completed</flux:badge>
                                        @break
                                    @case('cancelled')
                                        <flux:badge color="red" size="sm">Cancelled</flux:badge>
                                        @break
                                @endswitch
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $event->availableSlots() }} / {{ $event->max_slots }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" href="{{ route('organizer.events.show', $event) }}">View</flux:button>
                                    @if($event->status->value === 'draft')
                                        <flux:button size="sm" href="{{ route('organizer.events.edit', $event) }}">Edit</flux:button>
                                        <form method="POST" action="{{ route('organizer.events.publish', $event) }}" class="inline">
                                            @csrf
                                            <flux:button size="sm" variant="primary" type="submit">Publish</flux:button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No events found. <a href="{{ route('organizer.events.create') }}" class="text-blue-600 hover:underline dark:text-blue-400">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $events->links() }}
        </div>
    </div>
</x-layouts.app>
