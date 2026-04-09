<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            body { font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif; }
            .gradient-text {
                background: linear-gradient(135deg, #059669 0%, #0891b2 50%, #6366f1 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .auth-gradient {
                background: linear-gradient(135deg, #059669 0%, #0891b2 40%, #6366f1 100%);
            }
            .float-animation { animation: float 6s ease-in-out infinite; }
            @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        </style>
    </head>
    <body class="min-h-screen bg-gray-50 antialiased">
        <div class="flex min-h-screen">
            <!-- Left side — decorative travel panel -->
            <div class="hidden lg:flex lg:w-1/2 auth-gradient relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-16 left-16 text-8xl float-animation">⛰️</div>
                    <div class="absolute top-1/3 right-20 text-7xl float-animation" style="animation-delay:-2s">🏝️</div>
                    <div class="absolute bottom-1/3 left-1/4 text-6xl float-animation" style="animation-delay:-4s">🌊</div>
                    <div class="absolute bottom-20 right-1/4 text-7xl float-animation" style="animation-delay:-1s">🏔️</div>
                </div>
                <div class="relative z-10 flex flex-col justify-between p-12 text-white w-full">
                    <div>
                        <a href="/" class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold">Pinas<span class="text-emerald-200">Lakbay</span></span>
                        </a>
                    </div>
                    <div class="space-y-6">
                        <h2 class="text-4xl font-extrabold leading-tight">Organize adventures.<br>Build your community.</h2>
                        <p class="text-lg text-emerald-100 max-w-md leading-relaxed">Create events, manage bookings, and connect with thousands of Filipino explorers across the Philippines.</p>
                    </div>
                    <div class="text-xs text-emerald-200/60">&copy; {{ date('Y') }} PinasLakbay. All rights reserved.</div>
                </div>
            </div>

            <!-- Right side — form -->
            <div class="flex flex-1 flex-col items-center justify-center p-6 sm:p-12">
                <!-- Mobile logo -->
                <div class="lg:hidden mb-8">
                    <a href="/" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">Pinas<span class="gradient-text">Lakbay</span></span>
                    </a>
                </div>

                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
