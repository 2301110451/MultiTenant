@php
    $isCentral = \App\Support\Tenancy::isCentralHost(request()->getHost());
@endphp

@if($isCentral)
<x-central-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Set new password</h2>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Choose a strong password for your admin account.</p>
    </div>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">New password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition">
            @error('password')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition">
        </div>
        <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold shadow-lg shadow-blue-600/30 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300">Reset password</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400"><a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 font-medium hover:underline">Back to sign in</a></p>
</x-central-guest-layout>
@else
@php $tTheme = \App\Support\TenantAppearance::theme(); @endphp
<x-tenant-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Set new password</h2>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Choose a strong password for your account.</p>
    </div>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">New password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition">
            @error('password')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition">
        </div>
        <button type="submit" class="w-full py-3 rounded-xl text-white text-sm font-semibold shadow-lg transition {{ $tTheme['button'] }}">Reset password</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400"><a href="{{ route('login') }}" class="font-semibold {{ $tTheme['breadcrumbAccent'] }} hover:underline">Back to sign in</a></p>
</x-tenant-guest-layout>
@endif
