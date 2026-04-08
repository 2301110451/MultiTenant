@php
    $theme = \App\Support\TenantAppearance::theme();
    $tenant = \App\Support\Tenancy::currentTenant();
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant?->name ?? config('app.name') }} &mdash; Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
    </style>
</head>
<body class="h-full antialiased hero-gradient">

<div class="min-h-screen flex flex-col items-center justify-center px-6 py-16 relative overflow-hidden">
    {{-- Decorative blobs --}}
    <div class="absolute top-1/4 left-1/4 w-96 h-96 opacity-20 rounded-full blur-3xl bg-gradient-to-br from-indigo-600 to-violet-700 pointer-events-none"></div>
    <div class="absolute bottom-0 right-1/4 w-72 h-72 opacity-15 rounded-full blur-3xl bg-gradient-to-br from-amber-500 to-orange-600 pointer-events-none"></div>
    <div class="absolute top-10 right-10 w-40 h-40 opacity-10 rounded-full blur-3xl bg-gradient-to-br from-sky-500 to-cyan-600 pointer-events-none"></div>

    <div class="relative z-10 text-center max-w-2xl w-full">
        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest border rounded-full px-4 py-1.5 mb-8 {{ $theme['badge'] }}">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
            </svg>
            Barangay Portal
        </div>

        {{-- Icon --}}
        <div class="w-20 h-20 mx-auto mb-8 rounded-3xl flex items-center justify-center shadow-2xl {{ $theme['brandIcon'] }}">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
        </div>

        <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight mb-4">
            {{ $tenant?->name ?? 'Barangay' }}
        </h1>
        <p class="text-slate-400 text-lg leading-relaxed mb-2">
            Equipment and facility reservations for residents and staff.
        </p>
        @if($plan = \App\Support\Tenancy::tenantPlan())
            <p class="text-sm {{ $theme['heroAccent'] }} font-semibold mb-10">
                {{ $plan->name }} plan &mdash; themed portal experience
            </p>
        @else
            <div class="mb-10"></div>
        @endif

        {{-- CTA Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl text-white font-semibold text-sm shadow-lg transition {{ $theme['button'] }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                Sign in to portal
            </a>
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl border border-white/20 text-white font-semibold text-sm hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
                </svg>
                Create account
            </a>
        </div>

        {{-- Features strip --}}
        <div class="mt-14 grid grid-cols-3 gap-4 max-w-md mx-auto">
            <div class="text-center">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                </div>
                <p class="text-xs text-slate-500 font-medium">Easy booking</p>
            </div>
            <div class="text-center">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <p class="text-xs text-slate-500 font-medium">Instant approval</p>
            </div>
            <div class="text-center">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/>
                    </svg>
                </div>
                <p class="text-xs text-slate-500 font-medium">Full records</p>
            </div>
        </div>
    </div>

    <p class="relative z-10 mt-12 text-slate-600 text-xs">
        &copy; {{ date('Y') }} {{ $tenant?->name ?? '' }} &mdash; Barangay Reservation System
    </p>
</div>

</body>
</html>
