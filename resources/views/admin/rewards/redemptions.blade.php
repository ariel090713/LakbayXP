<x-layouts.admin>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">Reward Redemptions</flux:heading>
            <flux:button href="{{ route('admin.rewards.index') }}">Back to Rewards</flux:button>
        </div>

        @if(session('success'))
            <flux:callout variant="success">{{ session('success') }}</flux:callout>
        @endif

        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Reward</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Points</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-zinc-500">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($redemptions as $r)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $r->user->username }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $r->reward->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ number_format($r->points_spent) }} pts</td>
                            <td class="px-4 py-3 text-sm">
                                @switch($r->status->value)
                                    @case('pending') <flux:badge color="amber" size="sm">Pending</flux:badge> @break
                                    @case('approved') <flux:badge color="blue" size="sm">Approved</flux:badge> @break
                                    @case('claimed') <flux:badge color="green" size="sm">Claimed</flux:badge> @break
                                    @case('rejected') <flux:badge color="red" size="sm">Rejected</flux:badge> @break
                                @endswitch
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ $r->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    @if($r->status->value === 'pending')
                                        <form method="POST" action="{{ route('admin.rewards.redemptions.approve', $r) }}" class="inline">
                                            @csrf
                                            <flux:button size="sm" variant="primary" type="submit">Approve</flux:button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.rewards.redemptions.reject', $r) }}" class="inline">
                                            @csrf
                                            <flux:button size="sm" variant="danger" type="submit">Reject</flux:button>
                                        </form>
                                    @elseif($r->status->value === 'approved')
                                        <form method="POST" action="{{ route('admin.rewards.redemptions.claim', $r) }}" class="inline">
                                            @csrf
                                            <flux:button size="sm" variant="primary" type="submit">Mark Claimed</flux:button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">No redemptions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $redemptions->links() }}
    </div>
</x-layouts.admin>
