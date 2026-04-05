<x-layouts.app>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ $event->title }}</flux:heading>
            <div class="flex items-center gap-2">
                @if($event->status->value === 'draft')
                    <flux:button size="sm" href="{{ route('organizer.events.edit', $event) }}">Edit</flux:button>
                    <form method="POST" action="{{ route('organizer.events.publish', $event) }}" class="inline">
                        @csrf
                        <flux:button size="sm" variant="primary" type="submit">Publish</flux:button>
                    </form>
                @endif
                @if(in_array($event->status->value, ['published', 'full']))
                    <flux:button size="sm" href="{{ route('organizer.bookings.index', $event) }}">Manage Bookings</flux:button>
                    @if($event->event_date->isPast() || $event->event_date->isToday())
                        <form method="POST" action="{{ route('organizer.events.complete', $event) }}" class="inline">
                            @csrf
                            <flux:button size="sm" variant="primary" type="submit">Complete Event</flux:button>
                        </form>
                    @endif
                @endif
                <flux:button href="{{ route('organizer.events.index') }}">Back to Events</flux:button>
            </div>
        </div>

        @if(session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
                <flux:heading size="lg">Event Details</flux:heading>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Status</dt>
                        <dd>
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
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Place</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ $event->place->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Category</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ ucwords(str_replace('_', ' ', $event->category->value)) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Date</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ $event->event_date->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Meeting Place</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ $event->meeting_place ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Fee</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">₱{{ number_format($event->fee, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Available Slots</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ $event->availableSlots() }} / {{ $event->max_slots }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500 dark:text-zinc-400">Auto-Approve</dt>
                        <dd class="text-zinc-900 dark:text-zinc-100">{{ $event->auto_approve_bookings ? 'Yes' : 'No' }}</dd>
                    </div>
                </dl>
                @if($event->description)
                    <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $event->description }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-4 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
                <flux:heading size="lg">Bookings ({{ $event->bookings->count() }})</flux:heading>
                @if($event->bookings->count() > 0)
                    <div class="space-y-2">
                        @foreach($event->bookings as $booking)
                            <div class="flex items-center justify-between rounded border border-zinc-100 px-3 py-2 dark:border-zinc-800">
                                <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $booking->user->name }}</span>
                                @switch($booking->status->value)
                                    @case('pending')
                                        <flux:badge color="amber" size="sm">Pending</flux:badge>
                                        @break
                                    @case('approved')
                                        <flux:badge color="green" size="sm">Approved</flux:badge>
                                        @break
                                    @case('rejected')
                                        <flux:badge color="red" size="sm">Rejected</flux:badge>
                                        @break
                                    @case('cancelled')
                                        <flux:badge color="zinc" size="sm">Cancelled</flux:badge>
                                        @break
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No bookings yet.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
