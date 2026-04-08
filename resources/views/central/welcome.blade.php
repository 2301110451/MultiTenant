<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} &mdash; Central Administration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Anti-FOUC --}}
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
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); }
        .hero-gradient-light { background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #f5f3ff 100%); }
        .hero-pattern { background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%234338ca' fill-opacity='0.06'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10zm10 8c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm40 40c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z'/%3E%3C/g%3E%3C/svg%3E"); }
        .glow { box-shadow: 0 0 80px rgba(99,102,241,.25); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
        .float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body
    class="h-full antialiased"
    x-data="{
        dark: (localStorage.getItem('central-dark') === 'true') || (localStorage.getItem('central-dark') === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('central-dark', this.dark);
            document.documentElement.classList.toggle('dark', this.dark);
        }
    }"
    :class="dark ? 'hero-gradient hero-pattern' : 'hero-gradient-light hero-pattern'"
>

<div class="min-h-screen flex flex-col items-center justify-center px-6 py-16 relative overflow-hidden">

    {{-- Decorative orbs --}}
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Dark mode toggle (top-right corner) --}}
    <div class="absolute top-5 right-5 z-20">
        <button
            @click="toggleDark()"
            type="button"
            class="w-10 h-10 flex items-center justify-center rounded-xl border backdrop-blur-sm transition-all duration-200 hover:scale-105"
            :class="dark
                ? 'bg-white/10 border-white/20 text-yellow-300 hover:bg-white/20'
                : 'bg-indigo-900/40 border-indigo-500/30 text-indigo-200 hover:bg-indigo-900/60'"
            :title="dark ? 'Switch to light mode' : 'Switch to dark mode'"
        >
            <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
            </svg>
            <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
            </svg>
        </button>
    </div>

    {{-- Badge --}}
    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-widest rounded-full px-4 py-1.5 mb-8 border backdrop-blur-sm"
         :class="dark ? 'text-indigo-300 bg-indigo-600/20 border-indigo-500/30' : 'text-indigo-700 bg-indigo-100/80 border-indigo-300'">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
        </svg>
        Secure Government Portal
    </div>

    {{-- Icon --}}
    <div class="float mb-8">
        <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center glow shadow-2xl">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
        </div>
    </div>

    <h1 class="text-4xl sm:text-5xl font-extrabold text-center leading-tight mb-4"
        :class="dark ? 'text-white' : 'text-slate-900'">
        Barangay Equipment &amp;<br>
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Facility Reservation</span>
    </h1>

    <p class="text-center text-lg max-w-xl leading-relaxed mb-10"
       :class="dark ? 'text-slate-400' : 'text-slate-600'">
        Multi-tenant central administration system for managing barangay tenants, subscription plans, and system-wide operations across the Philippines.
    </p>

    {{-- CTA --}}
    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('central.apply') }}"
           class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl font-semibold text-sm backdrop-blur-sm transition-all duration-200 hover:scale-105"
           :class="dark ? 'bg-white/10 hover:bg-white/15 border border-white/20 text-white' : 'bg-white/70 hover:bg-white border border-indigo-200 text-indigo-700'">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Apply for a portal
        </a>
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-lg shadow-indigo-700/40 transition-all duration-200 hover:scale-105">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
            </svg>
            Sign in to Admin Panel
        </a>
    </div>

    {{-- Feature grid --}}
    <div class="mt-16 grid sm:grid-cols-3 gap-5 max-w-3xl w-full">
        @foreach([
            ['Multi-tenant', 'Domain-based isolation — each barangay gets its own database.', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5'],
            ['Subscription Plans', 'Basic, Standard, and Premium tiers with feature access control.', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['Live Provisioning', 'Create a new barangay portal in seconds — auto-migrates the tenant database.', 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'],
        ] as [$title, $desc, $icon])
        <div class="rounded-2xl p-5 backdrop-blur-sm border"
             :class="dark ? 'bg-white/5 border-white/10' : 'bg-white/60 border-indigo-100'">
            <div class="w-9 h-9 rounded-xl bg-indigo-600/20 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
            <p class="font-semibold text-sm mb-1" :class="dark ? 'text-white' : 'text-slate-800'">{{ $title }}</p>
            <p class="text-xs leading-relaxed" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ $desc }}</p>
        </div>
        @endforeach
    </div>

    <p class="mt-12 text-xs" :class="dark ? 'text-slate-600' : 'text-slate-500'">
        &copy; {{ date('Y') }} Republic of the Philippines &mdash; Barangay Information System
    </p>
</div>

</body>
</html>
