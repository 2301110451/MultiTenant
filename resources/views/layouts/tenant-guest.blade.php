<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant?->name ?? config('app.name') }} &mdash; Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Anti-FOUC: set dark class before any render --}}
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
        body { font-family: 'Inter', sans-serif; }
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234338ca' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .diagonal-lines {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.03) 10px,
                rgba(255,255,255,0.03) 20px
            );
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeSlideIn .45s ease both; }
        .delay-1 { animation-delay: .05s; }
    </style>
</head>
<body
    class="h-full antialiased"
    x-data="{
        dark: (localStorage.getItem('tenant-dark') === 'true') || (localStorage.getItem('tenant-dark') === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('tenant-dark', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        }
    }"
>
@php
    $badges = \App\Support\TenantAppearance::planSummaryBadges();
@endphp
<div class="min-h-screen flex">

    {{-- ── LEFT HERO PANEL (always dark) ──────────────────── --}}
    <div class="hidden lg:flex lg:w-[52%] xl:w-[55%] relative flex-col justify-between bg-gradient-to-br {{ $theme['heroGradient'] }} overflow-hidden">
        <div class="absolute inset-0 hero-pattern opacity-70"></div>
        <div class="absolute inset-0 diagonal-lines opacity-80"></div>
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full opacity-10 blur-3xl bg-gradient-to-br from-indigo-600 to-violet-700"></div>

        <div class="relative z-10 flex flex-col justify-center flex-1 px-16 py-20">
            <div class="flex items-center gap-4 mb-10">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg {{ $theme['brandIcon'] }}">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold tracking-widest {{ $theme['brandSub'] }} uppercase">Barangay portal</p>
                    <p class="text-white font-bold text-lg leading-tight">{{ $tenant?->name ?? 'Barangay' }}</p>
                </div>
            </div>

            @if($plan)
            <div class="mb-8">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border {{ $theme['badge'] }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $plan->name }} plan
                </span>
            </div>
            @endif

            <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-[1.15] mb-6">
                Equipment &amp;<br>
                <span class="{{ $theme['heroAccent'] }}">Facility</span><br>
                Reservations
            </h1>
            <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                Book barangay halls, courts, and equipment. Your experience matches your barangay's subscription tier.
            </p>

            @if(count($badges))
            <ul class="mt-10 space-y-3">
                @foreach(array_slice($badges, 0, 5) as $line)
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <svg class="w-5 h-5 shrink-0 {{ $theme['heroAccent'] }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $line }}
                    </li>
                @endforeach
            </ul>
            @endif
        </div>

        <div class="relative z-10 px-16 py-8 border-t border-slate-800">
            <p class="text-slate-500 text-xs">
                Secured barangay portal &mdash; {{ $tenant?->name ?? config('app.name') }}
            </p>
        </div>
    </div>

    {{-- ── RIGHT FORM PANEL ──────────────────────────────── --}}
    <div class="flex flex-1 flex-col justify-center relative bg-white dark:bg-slate-950 px-6 py-12 sm:px-12 lg:px-16 xl:px-24 transition-colors duration-200">

        {{-- Dark mode toggle (top-right) --}}
        <div class="absolute top-4 right-4">
            <button
                @click="toggleDark()"
                type="button"
                class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200 transition"
                :title="dark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <svg x-show="dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                </svg>
                <svg x-show="!dark" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                </svg>
            </button>
        </div>

        <div class="mx-auto w-full max-w-md">
            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $theme['brandIcon'] }}">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-900 dark:text-slate-100">{{ $tenant?->name ?? 'Barangay' }}</span>
            </div>

            <div class="animate-in">
                {{ $slot }}
            </div>
        </div>
    </div>

</div>
@stack('scripts')
</body>
</html>
