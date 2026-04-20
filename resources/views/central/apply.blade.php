<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Apply for a barangay portal &mdash; {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); }
        .hero-pattern { background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%234338ca' fill-opacity='0.06'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10zm10 8c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm40 40c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z'/%3E%3C/g%3E%3C/svg%3E"); }
        .glow { box-shadow: 0 0 80px rgba(99,102,241,.25); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="h-full antialiased hero-gradient hero-pattern text-slate-200">

<div
    x-data="{ open: @json($errors->any()) }"
    @keydown.escape.window="open = false"
    class="min-h-screen flex flex-col"
>
    {{-- Top bar --}}
    <header class="relative z-30 border-b border-white/10 bg-slate-950/40 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-white hover:text-indigo-300 transition">
                &larr; Back to home
            </a>
            <button
                type="button"
                @click="open = true"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-900/40 hover:bg-indigo-500"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Open application
            </button>
        </div>
    </header>

    <main class="relative z-10 flex flex-1 flex-col items-center px-6 py-16 pb-32">
        <div class="absolute top-1/4 left-1/4 h-96 w-96 rounded-full bg-indigo-600/10 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 h-72 w-72 rounded-full bg-violet-600/10 blur-3xl pointer-events-none"></div>

        <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-indigo-500/30 bg-indigo-600/20 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-indigo-300">
            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/></svg>
            Tenant onboarding
        </div>

        <div class="float mb-8">
            <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-indigo-500 to-violet-600 glow shadow-2xl">
                <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>

        <h1 class="mb-4 max-w-3xl text-center text-4xl font-extrabold leading-tight text-white sm:text-5xl">
            Apply for your<br>
            <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">barangay reservation portal</span>
        </h1>

        <p class="mb-10 max-w-2xl text-center text-lg leading-relaxed text-slate-400">
            Request a dedicated tenant space for your barangay: facilities, equipment, reservations, and role-based access for staff and residents.
            Submit the form and wait for super admin approval.
        </p>

        @if (session('success'))
            <div class="mb-10 w-full max-w-xl rounded-2xl border border-emerald-500/40 bg-emerald-950/50 px-5 py-4 text-sm text-emerald-200">
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="mb-12 flex flex-col gap-4 sm:flex-row sm:items-center">
            <button
                type="button"
                @click="open = true"
                class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-700/40 transition hover:scale-[1.02] hover:bg-indigo-700"
            >
                Start application
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </button>
            <a
                href="{{ route('login') }}"
                class="inline-flex items-center justify-center rounded-xl border border-white/20 px-8 py-3.5 text-sm font-semibold text-white/90 hover:bg-white/10"
            >
                Central admin sign in
            </a>
        </div>

        <div class="grid w-full max-w-5xl gap-5 sm:grid-cols-3">
            @foreach ([
                ['Secure isolation', 'Each barangay runs on its own database with domain-based routing.', 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.746 3.746 0 0121 12z'],
                ['Plans & limits', 'Basic, Standard, and Premium tiers control features and monthly reservation caps.', 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.147-2.146a9.2 9.2 0 00-1.227-1.21 9.28 9.28 0 00-1.525-.93 9.2 9.2 0 00-1.727-.63 9.28 9.28 0 00-1.9-.19 9.28 9.28 0 00-1.9.19 9.2 9.2 0 00-1.727.63 9.28 9.28 0 00-1.525.93 9.28 9.28 0 00-1.227 1.21l-2.147 2.146a11.95 11.95 0 00-5.814 5.519L2.25 18z'],
                ['Tenant account setup', 'Tenant Admin and optional Staff accounts are prepared on provisioning after approval.', 'M16.5 18.75h-9a2.25 2.25 0 01-2.25-2.25v-9A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25zM9.75 9.75h4.5m-4.5 3h4.5'],
            ] as [$title, $desc, $icon])
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600/20">
                        <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                        </svg>
                    </div>
                    <p class="mb-1 text-sm font-semibold text-white">{{ $title }}</p>
                    <p class="text-xs leading-relaxed text-slate-400">{{ $desc }}</p>
                </div>
            @endforeach
        </div>

        <p class="mt-16 text-center text-xs text-slate-600">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </p>
    </main>

    {{-- Floating action --}}
    <button
        type="button"
        @click="open = true"
        class="fixed bottom-6 right-6 z-40 flex items-center gap-3 rounded-full border border-indigo-400/40 bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-2xl shadow-indigo-900/50 transition hover:scale-105 hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/30"
        aria-label="Open application form"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Apply now
    </button>

    {{-- Modal --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="open = false"></div>

        <div
            @click.away="open = false"
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            class="relative z-10 flex max-h-[min(92vh,720px)] w-full max-w-lg flex-col overflow-hidden rounded-t-3xl border border-white/10 bg-slate-900 shadow-2xl sm:rounded-3xl"
        >
            <div class="flex items-start justify-between gap-4 border-b border-white/10 px-6 py-5">
                <div>
                    <h2 class="text-lg font-bold text-white">Register New Barangay</h2>
                    <p class="mt-1 text-xs text-slate-400">A tenant domain and separate database are created automatically from the name after super admin approval.</p>
                </div>
                <button type="button" @click="open = false" class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="post" action="{{ route('central.apply.store') }}" class="flex flex-1 flex-col overflow-y-auto">
                @csrf
                <div class="space-y-4 px-6 py-5">
                    <div>
                        <label for="barangay_name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Barangay Name *</label>
                        <input id="barangay_name" name="barangay_name" type="text" required value="{{ old('barangay_name') }}"
                               class="w-full rounded-xl border border-white/15 bg-slate-950/50 px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                               placeholder="e.g. Barangay Carmen">
                        @error('barangay_name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Full official name. Hostname is generated as slug.{{ $domainSuffix }} (or slug-2.{{ $domainSuffix }} if taken).
                        </p>
                    </div>
                    <div>
                        <label for="plan_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Subscription Plan</label>
                        <select id="plan_id" name="plan_id"
                                class="w-full rounded-xl border border-white/15 bg-slate-950/50 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                            <option value="">— No plan (Free) —</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>
                                    {{ $plan->name }} — {{ $plan->monthly_reservation_limit }} reservations/mo
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-2xl border border-white/15 bg-slate-950/40 p-4 space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Portal login accounts</h3>
                            <p class="mt-1 text-xs text-slate-400">
                                These users are created in the new tenant database. They sign in at the barangay hostname using tenant login.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-300">Tenant Admin</p>
                            <label class="block text-xs text-slate-300">Email *</label>
                            <input name="tenant_admin_email" type="email" required value="{{ old('tenant_admin_email') }}"
                                   class="w-full rounded-xl border border-white/15 bg-slate-950/60 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                            @error('tenant_admin_email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-slate-300">Password *</label>
                                    <input name="tenant_admin_password" type="password" required
                                           class="mt-1 w-full rounded-xl border border-white/15 bg-slate-950/60 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                                    @error('tenant_admin_password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-300">Confirm password *</label>
                                    <input name="tenant_admin_password_confirmation" type="password" required
                                           class="mt-1 w-full rounded-xl border border-white/15 bg-slate-950/60 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-white/10 pt-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-300">Staff (optional)</p>
                            <label class="block text-xs text-slate-300">Email</label>
                            <input name="staff_email" type="email" value="{{ old('staff_email') }}"
                                   class="w-full rounded-xl border border-white/15 bg-slate-950/60 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                            @error('staff_email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-slate-300">Password</label>
                                    <input name="staff_password" type="password"
                                           class="mt-1 w-full rounded-xl border border-white/15 bg-slate-950/60 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                                    @error('staff_password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-300">Confirm password</label>
                                    <input name="staff_password_confirmation" type="password"
                                           class="mt-1 w-full rounded-xl border border-white/15 bg-slate-950/60 px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Message (optional)</label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full resize-none rounded-xl border border-white/15 bg-slate-950/50 px-4 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                                  placeholder="Notes for the central team…">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-auto border-t border-white/10 bg-slate-950/80 px-6 py-4">
                    <button type="submit" class="w-full rounded-xl bg-indigo-600 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-900/40 hover:bg-indigo-500">
                        Submit application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
