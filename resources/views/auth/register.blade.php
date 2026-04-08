@php $tTheme = \App\Support\TenantAppearance::theme(); @endphp
@php
    $recaptchaEnabled = (bool) config('services.recaptcha.enabled')
        && (string) config('services.recaptcha.site_key') !== ''
        && (string) config('services.recaptcha.secret_key') !== '';
@endphp
<x-tenant-guest-layout>

    <div class="animate-in">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Create account</h2>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Register as a resident to request reservations and borrow equipment.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition {{ $errors->has('name') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900' }}">
            @error('name')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition {{ $errors->has('email') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900' }}">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition {{ $errors->has('password') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900' }}">
            @error('password')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900">
        </div>

        @if($recaptchaEnabled)
            <div>
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                @error('g-recaptcha-response')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl text-white text-sm font-semibold shadow-lg transition focus:outline-none focus:ring-4 focus:ring-indigo-200 {{ $tTheme['button'] }}">
            Register &amp; continue
        </button>

        <div class="relative py-1">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white dark:bg-slate-950 px-2 text-xs text-slate-400 dark:text-slate-500">or continue with</span>
            </div>
        </div>

        <a href="{{ route('tenant.google.redirect') }}"
           class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
            <svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.2-1.4 3.6-5.5 3.6-3.3 0-6-2.8-6-6.2s2.7-6.2 6-6.2c1.9 0 3.2.8 4 1.5l2.8-2.7C17.1 2.5 14.8 1.5 12 1.5 6.8 1.5 2.6 5.8 2.6 11s4.2 9.5 9.4 9.5c5.4 0 9-3.8 9-9.1 0-.6-.1-1-.1-1.2H12z"/>
            </svg>
            Continue with Google
        </a>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold {{ $tTheme['breadcrumbAccent'] }} hover:underline">Sign in</a>
    </p>

</x-tenant-guest-layout>

@if($recaptchaEnabled)
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endpush
@endif
