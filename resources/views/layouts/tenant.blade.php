<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}{{ $tenant?->name ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Anti-FOUC: apply dark class before any render --}}
    <script>
        (function () {
            var saved = localStorage.getItem('tenant-dark');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'true' || (saved === null && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body
    class="h-full bg-slate-50 dark:bg-slate-950 antialiased"
    x-data="{
        sidebarOpen: false,
        dark: (localStorage.getItem('tenant-dark') === 'true') || (localStorage.getItem('tenant-dark') === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('tenant-dark', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        }
    }"
>

{{-- ===================== SIDEBAR ===================== --}}
<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-slate-900 dark:bg-slate-950 border-r border-slate-800 transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>
    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-md {{ $theme['brandIcon'] }}">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
        </div>
        <div class="leading-tight min-w-0">
            <p class="text-white font-bold text-sm truncate">{{ \Illuminate\Support\Str::limit($tenant?->name ?? 'Barangay', 22) }}</p>
            <p class="{{ $theme['brandSub'] }} text-xs font-medium">{{ $plan?->name ?? 'Plan' }} &middot; Portal</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5">
        <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Main</p>

        @php
            $tenantUser = auth('tenant')->user();
            $canManageTenant = $tenantUser?->isSecretary() || $tenantUser?->isCaptain();
            $navItems = [
                ['route' => 'dashboard',                   'label' => 'Dashboard',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'tenant.facilities.index',     'label' => $canManageTenant ? 'Facilities' : 'Available Facilities',   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'tenant.reservations.index',   'label' => 'Reservations', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
                ['route' => 'tenant.calendar',             'label' => 'Calendar',     'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
            ];
            if ($canManageTenant && $planAllowsReports) {
                $navItems[] = ['route' => 'tenant.reports.index', 'label' => 'Reports', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'];
            }
        @endphp

        @foreach($navItems as $item)
            @php $active = request()->routeIs($item['route'].'*') || ($item['route'] === 'tenant.calendar' && request()->routeIs('tenant.calendar')); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ $active ? $theme['navActive'] : $theme['navIdle'] }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach

        <div class="my-4 border-t border-slate-800"></div>
        <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Account</p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-red-400 hover:bg-slate-800 transition-all duration-150">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                Sign out
            </button>
        </form>
    </nav>

    {{-- User info --}}
    <div class="px-4 py-4 border-t border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full {{ $theme['avatar'] }} flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ strtoupper(substr(auth('tenant')->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-white text-xs font-semibold truncate">{{ auth('tenant')->user()?->name }}</p>
                <p class="text-slate-500 text-xs truncate">{{ str_replace('_', ' ', auth('tenant')->user()?->role->value ?? '') }}</p>
            </div>
        </div>
    </div>
</aside>

{{-- Mobile overlay --}}
<div class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
     x-show="sidebarOpen"
     x-transition
     @click="sidebarOpen = false"
     style="display:none">
</div>

{{-- ===================== MAIN AREA ===================== --}}
<div class="lg:pl-64 flex flex-col min-h-screen">

    {{-- Top bar --}}
    <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 sm:px-8 shadow-sm">
        {{-- Mobile menu button --}}
        <button type="button" @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
            </svg>
        </button>

        {{-- Breadcrumb --}}
        <div class="hidden lg:flex items-center gap-2 text-sm">
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ \Illuminate\Support\Str::limit($tenant?->name ?? 'Barangay', 28) }}</span>
            @if($breadcrumb)
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="{{ $theme['breadcrumbAccent'] }} font-semibold">{{ $breadcrumb }}</span>
            @endif
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-2 ml-auto">
            @if($plan)
            <span class="hidden sm:inline-flex text-xs font-semibold px-2.5 py-1 rounded-full border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800">
                {{ $plan->name }}
            </span>
            @endif

            {{-- Dark mode toggle --}}
            <button
                @click="toggleDark()"
                type="button"
                class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition"
                :title="dark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                {{-- Sun icon (shown in dark mode) --}}
                <svg x-show="dark" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                </svg>
                {{-- Moon icon (shown in light mode) --}}
                <svg x-show="!dark" class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                </svg>
            </button>

            {{-- User dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <div class="w-7 h-7 rounded-full {{ $theme['avatar'] }} flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr(auth('tenant')->user()?->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ auth('tenant')->user()?->name }}
                    </span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-52 bg-white dark:bg-slate-900 rounded-xl shadow-lg ring-1 ring-slate-200 dark:ring-slate-700 py-1 z-50"
                     style="display:none">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ auth('tenant')->user()?->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ auth('tenant')->user()?->email }}</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 capitalize">{{ str_replace('_', ' ', auth('tenant')->user()?->role->value ?? '') }}</p>
                    </div>
                    <div class="py-1">
                        <button
                            @click="toggleDark(); open = false"
                            type="button"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800"
                        >
                            <svg x-show="dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                            </svg>
                            <svg x-show="!dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                            </svg>
                            <span x-text="dark ? 'Light mode' : 'Dark mode'"></span>
                        </button>
                    </div>
                    <div class="border-t border-slate-100 dark:border-slate-800 py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 page-fade bg-slate-50 dark:bg-slate-950">
        {{ $slot }}
    </main>

    <footer class="py-4 px-8 text-center text-xs text-slate-400 dark:text-slate-600 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
        &copy; {{ date('Y') }} {{ $tenant?->name ?? config('app.name') }} &mdash; Barangay Reservation Portal
    </footer>
</div>

@stack('scripts')
</body>
</html>
