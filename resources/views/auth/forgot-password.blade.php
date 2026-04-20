@php
    $isCentral = \App\Support\Tenancy::isCentralHost(request()->getHost());
@endphp

@if($isCentral)
<x-central-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Reset password</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">We will email you a link to choose a new password.</p>
    </div>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-[#3181E5] focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full py-3 rounded-xl bg-[#3181E5] hover:bg-blue-700 text-white text-sm font-semibold shadow-lg shadow-blue-600/30">Email reset link</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400"><a href="{{ route('login') }}" class="text-[#3181E5] dark:text-blue-400 font-medium">Back to sign in</a></p>
</x-central-guest-layout>
@else
@php $tTheme = \App\Support\TenantAppearance::theme(); @endphp
<x-tenant-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Reset password</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">We will email you a link to choose a new password.</p>
    </div>
    @if (session('status'))
        <div class="mb-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-[#3181E5] focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full py-3 rounded-xl text-white text-sm font-semibold shadow-lg transition {{ $tTheme['button'] }}">Email reset link</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400"><a href="{{ route('login') }}" class="font-semibold {{ $tTheme['breadcrumbAccent'] }}">Back to sign in</a></p>
</x-tenant-guest-layout>
@endif
