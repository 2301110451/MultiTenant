@php
    $isCentral = \App\Support\Tenancy::isCentralHost(request()->getHost());
    $tTheme = \App\Support\TenantAppearance::theme();
@endphp

@if($isCentral)
<x-central-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Set new password</h2>
    </div>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">New password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            @error('password')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
        </div>
        <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">Reset password</button>
    </form>
</x-central-guest-layout>
@else
<x-tenant-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Set new password</h2>
    </div>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">New password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            @error('password')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
        </div>
        <button type="submit" class="w-full py-3 rounded-xl text-white text-sm font-semibold shadow-lg transition {{ $tTheme['button'] }}">Reset password</button>
    </form>
</x-tenant-guest-layout>
@endif
