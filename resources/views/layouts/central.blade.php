<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name', 'Barangay Reservation') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (function () {
            var saved = localStorage.getItem('central-dark');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'true' || (saved === null && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, body { font-family: 'Inter', sans-serif; }
        .sidebar-gradient { background: linear-gradient(180deg, #0F172A 0%, #020617 50%, #1E293B 100%); }
        .nav-active { background: linear-gradient(90deg, #6366F1, #8B5CF6); box-shadow: 0 4px 14px rgba(99,102,241,.30); }
        @keyframes pageFade { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
        .page-fade { animation: pageFade .35s ease both; }
    </style>
</head>
<body
    class="h-full antialiased"
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('central-sidebar-collapsed') === 'true',
        dark: (localStorage.getItem('central-dark') === 'true') || (localStorage.getItem('central-dark') === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('central-dark', this.dark);
            document.documentElement.classList.add('theme-transitioning');
            document.documentElement.classList.toggle('dark', this.dark);
            setTimeout(() => document.documentElement.classList.remove('theme-transitioning'), 400);
        },
        toggleCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('central-sidebar-collapsed', this.sidebarCollapsed);
        }
    }"
>

{{-- SIDEBAR --}}
<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col sidebar-gradient border-r border-white/[0.06] transition-all duration-300 lg:translate-x-0"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-[72px]' : 'w-64'
    ]"
>
    <div class="flex items-center gap-3 px-4 py-5 border-b border-white/[0.06]" :class="sidebarCollapsed && 'justify-center'">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shrink-0 shadow-lg shadow-indigo-900/40 ring-1 ring-white/10">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
        </div>
        <div class="leading-tight min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
            <p class="text-white font-bold text-sm truncate">BRGY Reservation</p>
            <p class="text-indigo-400 text-xs font-medium">Central Admin</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5">
        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-3" :class="sidebarCollapsed ? 'text-center px-0' : 'px-3'" x-text="sidebarCollapsed ? '•' : 'Main'"></p>

        @php
            $navItems = [
                ['route' => 'dashboard',              'label' => 'Dashboard',   'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'central.tenants.index', 'label' => 'Barangays',   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'central.tenant-applications.index', 'match' => 'central.tenant-applications.*', 'label' => 'Applications', 'icon' => 'M3.75 5.25a3 3 0 013-3h10.5a3 3 0 013 3v13.5a3 3 0 01-3 3H6.75a3 3 0 01-3-3V5.25zM7.5 8.25h9m-9 3h9m-9 3h5.25'],
                ['route' => 'central.subscription-intents.index', 'match' => 'central.subscription-intents.*', 'label' => 'Sub. requests', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z'],
                ['route' => 'central.plans.index',   'label' => 'Plans',       'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['route' => 'central.support-tickets.index',   'label' => 'Support',       'icon' => 'M8 10h8m-8 4h5m4-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'central.update-announcements.index',   'label' => 'Updates',       'icon' => 'M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'central.system-versions.index',   'label' => 'Versions',       'icon' => 'M11.25 3.75h1.5m-6 0h.008v.008H5.25V3.75zm13.5 0h.008v.008h-.008V3.75zM3.75 7.5h16.5v12.75H3.75V7.5z'],
            ];
        @endphp

        @foreach($navItems as $item)
            @php $active = request()->routeIs($item['match'] ?? $item['route'].'*'); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 rounded-xl text-sm font-medium transition-all duration-150
                      {{ $active ? 'nav-active text-white' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}"
               :class="sidebarCollapsed ? 'px-0 py-2.5 justify-center' : 'px-3 py-2.5'"
               @if(!$active) :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''" @endif>
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <div class="my-4 border-t border-white/[0.06]"></div>
        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-3" :class="sidebarCollapsed ? 'text-center px-0' : 'px-3'" x-text="sidebarCollapsed ? '•' : 'Account'"></p>

        @if(Route::has('profile.edit'))
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-3 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-white/[0.06] transition-all duration-150"
           :class="sidebarCollapsed ? 'px-0 py-2.5 justify-center' : 'px-3 py-2.5'">
            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-transition.opacity>Profile</span>
        </a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 rounded-xl text-sm font-medium text-slate-400 hover:text-red-400 hover:bg-white/[0.06] transition-all duration-150"
                    :class="sidebarCollapsed ? 'px-0 py-2.5 justify-center' : 'px-3 py-2.5'">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity>Sign out</span>
            </button>
        </form>
    </nav>

    {{-- Collapse toggle (desktop only) --}}
    <button type="button"
            @click="toggleCollapse()"
            class="hidden lg:flex items-center justify-center w-full py-3 border-t border-white/[0.06] text-slate-500 hover:text-slate-300 transition-colors">
        <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/>
        </svg>
    </button>

    {{-- User chip at bottom --}}
    <div class="px-4 py-4 border-t border-white/[0.06]">
        <div class="flex items-center gap-3" :class="sidebarCollapsed && 'justify-center'">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-xs font-bold shrink-0 ring-2 ring-indigo-500/20">
                {{ strtoupper(substr(auth('web')->user()?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1" x-show="!sidebarCollapsed" x-transition.opacity>
                <p class="text-white text-xs font-semibold truncate">{{ auth('web')->user()?->name }}</p>
                <p class="text-indigo-400/70 text-[10px] font-medium">Super Admin</p>
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

{{-- MAIN AREA --}}
<div class="flex flex-col min-h-screen transition-all duration-300" :class="sidebarCollapsed ? 'lg:pl-[72px]' : 'lg:pl-64'">

    {{-- Top bar --}}
    <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200/80 dark:border-slate-800/60 bg-white/80 dark:bg-slate-900/80 px-4 sm:px-8 backdrop-blur-xl">
        {{-- Mobile hamburger --}}
        <button type="button" @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
            </svg>
        </button>

        {{-- Breadcrumb --}}
        <div class="hidden lg:flex items-center gap-2 text-sm">
            <span class="font-semibold text-slate-700 dark:text-slate-200">Central Admin</span>
            @if($breadcrumb)
                <svg class="w-4 h-4 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $breadcrumb }}</span>
            @endif
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-2 ml-auto">

            {{-- Dark mode toggle --}}
            <button
                @click="toggleDark()"
                type="button"
                class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition-all hover:scale-105"
                :title="dark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <svg x-show="dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                </svg>
                <svg x-show="!dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                </svg>
            </button>

            {{-- User dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-xs font-bold ring-2 ring-indigo-500/10">
                        {{ strtoupper(substr(auth('web')->user()?->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ auth('web')->user()?->name }}
                    </span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-overlay ring-1 ring-slate-200/60 dark:ring-slate-700/60 py-1.5 z-50"
                     style="display:none">
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ auth('web')->user()?->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ auth('web')->user()?->email }}</p>
                        <span class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 pulse-glow"></span>
                            Super Admin
                        </span>
                    </div>
                    <div class="py-1">
                        <button
                            @click="toggleDark(); open = false"
                            type="button"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        >
                            <svg x-show="dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                            </svg>
                            <svg x-show="!dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                            </svg>
                            <span x-text="dark ? 'Light mode' : 'Dark mode'"></span>
                        </button>
                        @if(Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                            Profile settings
                        </a>
                        @endif
                    </div>
                    <div class="border-t border-slate-100 dark:border-slate-800 py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
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
    <main class="flex-1 page-fade">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="py-4 px-8 text-center text-xs text-slate-400 dark:text-slate-600 border-t border-slate-100 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm">
        &copy; {{ date('Y') }} Barangay Reservation System &mdash; Central Administration Panel
    </footer>
</div>

@stack('scripts')
</body>
</html>
