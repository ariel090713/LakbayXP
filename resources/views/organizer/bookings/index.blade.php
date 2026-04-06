<x-layouts.organizer>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Bookings — {{ $event->title }}</flux:heading>
            <flux:button href="{{ route('organizer.events.show', $event) }}">Back to Event</flux:button>
        </div>

        @if(session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        @if(session('error'))
            <flux:callout variant="danger">
                {{ session('error') }}
            </flux:callout>
        @endif

        <div class="text-sm text-zinc-500 dark:text-zinc-400">
            Available Slots: {{ $event->availableSlots() }} / {{ $event->max_slots }}
        </div>

        {{-- Pending Bookings --}}
        <div class="space-y-4 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg">Pending Bookings</flux:heading>
            @php
                $pendingBookings = $event->bookings->where('status.value', 'pending');
            @endphp
            @if($pendingBookings->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>User</flux:table.column>
                        <flux:table.column>Email</flux:table.column>
                        <flux:table.column>Booked At</flux:table.column>
                        <flux:table.column>Actions</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($pendingBookings as $booking)
                            <flux:table.row>
                                <flux:table.cell>{{ $booking->user->name }}</flux:table.cell>
                                <flux:table.cell>{{ $booking->user->email }}</flux:table.cell>
                                <flux:table.cell>{{ $booking->created_at->format('M d, Y H:i') }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('organizer.bookings.approve', $booking) }}" class="inline">
                                            @csrf
                                            <flux:button size="sm" variant="primary" type="submit">Approve</flux:button>
                                        </form>
                                        <form method="POST" action="{{ route('organizer.bookings.reject', $booking) }}" class="inline">
                                            @csrf
                                            <flux:button size="sm" variant="danger" type="submit">Reject</flux:button>
                                        </form>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No pending bookings.</p>
            @endif
        </div>

        {{-- Approved Bookings --}}
        <div class="space-y-4 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg">Approved Bookings</flux:heading>
            @php
                $approvedBookings = $event->bookings->where('status.value', 'approved');
            @endphp
            @if($approvedBookings->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>User</flux:table.column>
                        <flux:table.column>Email</flux:table.column>
                        <flux:table.column>Approved At</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($approvedBookings as $booking)
                            <flux:table.row>
                                <flux:table.cell>{{ $booking->user->name }}</flux:table.cell>
                                <flux:table.cell>{{ $booking->user->email }}</flux:table.cell>
                                <flux:table.cell>{{ $booking->approved_at?->format('M d, Y H:i') ?? '—' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No approved bookings.</p>
            @endif
        </div>

        {{-- Rejected Bookings --}}
        <div class="space-y-4 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg">Rejected Bookings</flux:heading>
            @php
                $rejectedBookings = $event->bookings->where('status.value', 'rejected');
            @endphp
            @if($rejectedBookings->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>User</flux:table.column>
                        <flux:table.column>Email</flux:table.column>
                        <flux:table.column>Rejected At</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($rejectedBookings as $booking)
                            <flux:table.row>
                                <flux:table.cell>{{ $booking->user->name }}</flux:table.cell>
                                <flux:table.cell>{{ $booking->user->email }}</flux:table.cell>
                                <flux:table.cell>{{ $booking->rejected_at?->format('M d, Y H:i') ?? '—' }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No rejected bookings.</p>
            @endif
        </div>
    </div>
</x-layouts.organizer>
