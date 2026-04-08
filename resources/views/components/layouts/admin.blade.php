<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #059669, #0891b2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link:hover, .sidebar-link.active { background: linear-gradient(135deg, rgba(5,150,105,0.08), rgba(8,145,178,0.08)); color: #059669; }
        .sidebar-link.active { border-right: 3px solid #059669; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:w-64 flex-col bg-white border-r border-gray-100 fixed inset-y-0 left-0 z-30">
            <div class="h-16 flex items-center px-6 border-b border-gray-100">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-gray-900">Lakbay<span class="gradient-text">XP</span></span>
                        <span class="block text-[10px] font-semibold text-gray-400 -mt-1 tracking-wider uppercase">Admin</span>
                    </div>
                </a>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p class="px-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Overview</p>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <p class="px-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-6 mb-2">Content</p>
                <a href="{{ route('admin.places.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.places.*') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Places
                </a>
                <a href="{{ route('admin.badges.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.badges.*') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Badges
                </a>
                <a href="{{ route('admin.rewards.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.rewards.*') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                    Rewards
                </a>
                <a href="{{ route('admin.events.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.events.*') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Events
                </a>
                <a href="{{ route('admin.xp.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.xp.*') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    XP Management
                </a>
                <a href="{{ route('admin.settings') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>

                <p class="px-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-6 mb-2">People</p>
                <a href="{{ route('admin.organizers.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.organizers.*') ? 'active text-emerald-700' : 'text-gray-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Organizers
                </a>
            </nav>

            <div class="p-3 border-t border-gray-100">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-9 h-9 rounded-full bg-gray-900 flex items-center justify-center text-white text-sm font-bold">
                        {{ auth()->user()->initials() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-400">Admin</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-red-600 hover:bg-red-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 lg:ml-64">
            <div class="lg:hidden h-16 bg-white border-b border-gray-100 flex items-center px-4">
                <span class="font-bold text-gray-900">Lakbay<span class="gradient-text">XP</span> <span class="text-xs text-gray-400">Admin</span></span>
            </div>
            <div class="p-6 lg:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>
    <script src="/js/image-preview.js"></script>
    @fluxScripts
    <script>
        // Disable Livewire SPA navigation for admin pages (needed for file uploads)
        document.addEventListener('livewire:navigate', (e) => { e.preventDefault(); });
    </script>
</body>
</html>
