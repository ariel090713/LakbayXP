<x-layouts.admin>
    <div class="space-y-6">
        <flux:heading size="xl">Admin Dashboard</flux:heading>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Places</div>
                <div class="mt-2 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalPlaces }}</div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Events</div>
                <div class="mt-2 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalEvents }}</div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Users</div>
                <div class="mt-2 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalUsers }}</div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Organizers</div>
                <div class="mt-2 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalOrganizers }}</div>
            </div>
        </div>
    </div>
</x-layouts.admin>
