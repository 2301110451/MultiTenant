<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Centralized multi-tenant administration system for managing barangay portals across the Philippines.">
    <title>{{ config('app.name') }} &mdash; Central Administration</title>
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
        body { font-family: 'Inter', sans-serif; }
        .hero-dark { background: linear-gradient(135deg, #0F172A 0%, #020617 50%, #1E293B 100%); }
        .hero-light { background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 30%, #F8FAFC 100%); }
        .dot-pattern {
            background-image: radial-gradient(circle, rgba(99,102,241,0.08) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-12px); }
        }
        .float-anim { animation: float 5s ease-in-out infinite; }
        .icon-glow { box-shadow: 0 0 60px rgba(99,102,241,.30), 0 20px 40px rgba(99,102,241,.18); }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up   { animation: fadeUp .6s ease both; }
        .delay-1   { animation-delay: .1s; }
        .delay-2   { animation-delay: .2s; }
        .delay-3   { animation-delay: .35s; }
        .delay-4   { animation-delay: .5s; }
    </style>
</head>
<body
    class="h-full antialiased transition-colors duration-300"
    x-data="{
        dark: (localStorage.getItem('central-dark') === 'true') || (localStorage.getItem('central-dark') === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('central-dark', this.dark);
            document.documentElement.classList.add('theme-transitioning');
            document.documentElement.classList.toggle('dark', this.dark);
            setTimeout(() => document.documentElement.classList.remove('theme-transitioning'), 400);
        }
    }"
    :class="dark ? 'hero-dark dot-pattern' : 'hero-light dot-pattern'"
>

<div class="min-h-screen flex flex-col items-center justify-center px-6 py-16 relative overflow-hidden">

    {{-- Decorative orbs --}}
    <div class="absolute top-1/4 left-[10%] w-80 h-80 rounded-full blur-3xl pointer-events-none"
         :class="dark ? 'bg-indigo-600/10' : 'bg-indigo-400/15'"></div>
    <div class="absolute bottom-1/4 right-[10%] w-64 h-64 rounded-full blur-3xl pointer-events-none"
         :class="dark ? 'bg-violet-500/8' : 'bg-violet-300/15'"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full blur-3xl pointer-events-none"
         :class="dark ? 'bg-cyan-500/[0.03]' : 'bg-cyan-300/10'"></div>

    {{-- Dark mode toggle --}}
    <div class="absolute top-5 right-5 z-20">
        <button
            @click="toggleDark()"
            type="button"
            class="w-10 h-10 flex items-center justify-center rounded-xl border backdrop-blur-sm transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-400"
            :class="dark
                ? 'bg-white/[0.06] border-white/[0.12] text-yellow-300 hover:bg-white/[0.12]'
                : 'bg-indigo-600/10 border-indigo-300/40 text-indigo-700 hover:bg-indigo-600/20'"
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

    {{-- Security badge --}}
    <div class="fade-up flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest rounded-full px-4 py-1.5 mb-8 border backdrop-blur-sm"
         :class="dark ? 'text-indigo-300 bg-indigo-600/15 border-indigo-500/25' : 'text-indigo-700 bg-indigo-100 border-indigo-300'">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
        </svg>
        Secure Government Portal
    </div>

    {{-- Floating icon --}}
    <div class="float-anim fade-up delay-1 mb-8">
        <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center icon-glow shadow-2xl ring-1 ring-white/10">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
        </div>
    </div>

    {{-- Heading --}}
    <h1 class="fade-up delay-1 text-4xl sm:text-5xl font-extrabold text-center leading-tight mb-4"
        :class="dark ? 'text-white' : 'text-slate-900'">
        Barangay Equipment &amp;<br>
        <span class="bg-gradient-to-r from-indigo-500 via-violet-500 to-cyan-500 bg-clip-text text-transparent">Facility Reservation</span>
    </h1>

    <p class="fade-up delay-2 text-center text-lg max-w-xl leading-relaxed mb-10"
       :class="dark ? 'text-slate-400' : 'text-slate-600'">
        Multi-tenant central administration system for managing barangay tenants, subscription plans, and system-wide operations across the Philippines.
    </p>

    {{-- CTA Buttons --}}
    <div class="fade-up delay-2 flex flex-col sm:flex-row gap-4">
        <a href="{{ route('central.apply') }}"
           class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-xl font-semibold text-sm border backdrop-blur-sm transition-all duration-200 hover:scale-105 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-400"
           :class="dark ? 'bg-white/[0.06] hover:bg-white/[0.12] border-white/[0.12] text-white' : 'bg-white/80 hover:bg-white border-indigo-200 text-indigo-700 shadow-sm'">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Apply for a portal
        </a>
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:scale-105 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
            </svg>
            Sign in to Admin Panel
        </a>
        <a href="{{ route('tenant.login.selector') }}"
           class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-xl font-semibold text-sm border backdrop-blur-sm transition-all duration-200 hover:scale-105 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-indigo-400"
           :class="dark ? 'bg-white/[0.06] hover:bg-white/[0.12] border-white/[0.12] text-white' : 'bg-white/80 hover:bg-white border-indigo-200 text-indigo-700 shadow-sm'">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75a17.933 17.933 0 0 1-7.5-1.632Z"/>
            </svg>
            Tenant Portal Login
        </a>
    </div>

    {{-- Feature grid --}}
    <div class="fade-up delay-3 mt-16 grid sm:grid-cols-3 gap-5 max-w-3xl w-full">
        @foreach([
            ['Multi-tenant', 'Domain-based isolation — each barangay gets its own database.', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5'],
            ['Subscription Plans', 'Basic, Standard, and Premium tiers with feature access control.', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['Live Provisioning', 'Create a new barangay portal in seconds — auto-migrates the tenant database.', 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'],
        ] as [$title, $desc, $icon])
        <div class="rounded-2xl p-6 backdrop-blur-sm border transition-all duration-300 hover:scale-[1.03] hover:-translate-y-1 group"
             :class="dark ? 'bg-white/[0.04] border-white/[0.06] hover:bg-white/[0.07] hover:border-indigo-500/20' : 'bg-white/70 border-slate-200/80 hover:bg-white hover:border-indigo-200 shadow-sm hover:shadow-md'">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/15 flex items-center justify-center mb-4 group-hover:bg-indigo-500/25 transition-colors">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
            <p class="font-bold text-sm mb-1.5" :class="dark ? 'text-white' : 'text-slate-800'">{{ $title }}</p>
            <p class="text-xs leading-relaxed" :class="dark ? 'text-slate-400' : 'text-slate-500'">{{ $desc }}</p>
        </div>
        @endforeach
    </div>

    <p class="fade-up delay-4 mt-14 text-xs" :class="dark ? 'text-slate-600' : 'text-slate-400'">
        &copy; {{ date('Y') }} Republic of the Philippines &mdash; Barangay Information System
    </p>
</div>

</body>
</html>
