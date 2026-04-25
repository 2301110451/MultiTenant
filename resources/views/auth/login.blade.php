@php
    $isCentral = \App\Support\Tenancy::isCentralHost(request()->getHost());
@endphp

@if($isCentral)
{{-- ════════════════════════════════════════════════════════════════
     CENTRAL ADMIN LOGIN  — beautiful split-screen
════════════════════════════════════════════════════════════════ --}}
<x-central-guest-layout>

    {{-- Session status --}}
    @if (session('status'))
        <div class="mb-6 flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email address</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="text-slate-400" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                </div>
                <input id="email" name="email" type="email"
                       value="{{ old('email') }}"
                       required autofocus autocomplete="username"
                       class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition
                              {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}"
                       placeholder="admin@central.example">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">Forgot password?</a>
                @endif
            </div>
            <div class="relative" x-data="{ show: false }">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
                <input id="password" name="password"
                       :type="show ? 'text' : 'password'"
                       required autocomplete="current-password"
                       class="w-full pl-10 pr-10 py-2.5 text-sm border rounded-xl transition
                              {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100' }}"
                       placeholder="••••••••••">
                <button type="button" @click="show = !show" tabindex="-1"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" style="width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <svg x-show="show" style="width:16px;height:16px;display:none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Remember --}}
        <div class="flex items-center">
            <input id="remember_me" name="remember" type="checkbox"
                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
            <label for="remember_me" class="ml-2.5 text-sm text-slate-600 dark:text-slate-400">Keep me signed in</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl
                       bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 active:from-indigo-800 active:to-violet-800
                       text-white text-sm font-semibold
                       shadow-lg shadow-indigo-600/30
                       transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-4 focus:ring-indigo-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
            </svg>
            Sign in to Admin Panel
        </button>
    </form>

</x-central-guest-layout>

@else
@php $tTheme = \App\Support\TenantAppearance::theme(); @endphp
@php
    $recaptchaEnabled = (bool) config('services.recaptcha.enabled')
        && (string) config('services.recaptcha.site_key') !== ''
        && (string) config('services.recaptcha.secret_key') !== '';
    $googleEnabled = (string) config('services.google.client_id') !== ''
        && (string) config('services.google.client_secret') !== '';
@endphp
<x-tenant-guest-layout>

    <div class="animate-in">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Welcome back</h2>
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Sign in to the barangay reservation portal</p>
    </div>

    @if (session('status'))
        <div class="mt-6 flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form id="tenant-login-form" method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email address</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="text-slate-400" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                </div>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition dark:bg-slate-800 dark:text-slate-100
                              {{ $errors->has('email') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900' }}">
            </div>
            @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">Forgot password?</a>
            </div>
            <div class="relative" x-data="{ show: false }">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
                <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                       class="w-full pl-10 pr-10 py-2.5 text-sm border rounded-xl transition dark:bg-slate-800 dark:text-slate-100
                              {{ $errors->has('password') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900' }}">
                <button type="button" @click="show = !show" tabindex="-1" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" style="width:16px;height:16px" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <svg x-show="show" style="width:16px;height:16px;display:none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
            @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center">
            <input id="remember_me" name="remember" type="checkbox" class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
            <label for="remember_me" class="ml-2.5 text-sm text-slate-600 dark:text-slate-400">Keep me signed in</label>
        </div>

        @if($recaptchaEnabled)
            <div>
                <input type="hidden" name="g-recaptcha-response" id="tenant-login-recaptcha-token">
                @error('g-recaptcha-response')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl text-white text-sm font-semibold shadow-lg transition focus:outline-none focus:ring-4 focus:ring-indigo-200 {{ $tTheme['button'] }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
            </svg>
            Sign in to portal
        </button>

        @if($googleEnabled)
            <div class="flex items-center gap-3">
                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">or</span>
                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
            </div>

            <a href="{{ route('tenant.google.redirect') }}"
               class="w-full inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-100 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.24 1.26-.95 2.33-2.01 3.05l3.25 2.52c1.9-1.75 2.99-4.34 2.99-7.42 0-.72-.07-1.41-.2-2.08H12z"/>
                    <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.61-2.43l-3.25-2.52c-.9.6-2.05.96-3.36.96-2.58 0-4.76-1.75-5.54-4.1H3.1v2.58A9.99 9.99 0 0 0 12 22z"/>
                    <path fill="#FBBC05" d="M6.46 13.91a5.98 5.98 0 0 1 0-3.82V7.51H3.1a9.99 9.99 0 0 0 0 8.98l3.36-2.58z"/>
                    <path fill="#4285F4" d="M12 6.01c1.47 0 2.78.5 3.82 1.5l2.86-2.86C16.95 3.04 14.69 2 12 2 8.1 2 4.74 4.24 3.1 7.51l3.36 2.58c.78-2.35 2.96-4.08 5.54-4.08z"/>
                </svg>
                Continue with Google
            </a>
        @endif

    </form>

    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        No account yet?
        <a href="{{ route('register') }}" class="font-semibold {{ $tTheme['breadcrumbAccent'] }} hover:underline">Create a resident account</a>
    </p>

    <p class="mt-12 text-center text-xs text-slate-400 dark:text-slate-500">
        This portal uses your barangay subscription plan for features and limits.
    </p>

    {{-- @push must be inside the layout slot so it runs before tenant-guest @stack('scripts'); pushes after </x-tenant-guest-layout> are too late and never render. --}}
    @if($recaptchaEnabled)
        @push('scripts')
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}" async defer></script>
            <script>
                (function () {
                    const form = document.getElementById('tenant-login-form');
                    const tokenInput = document.getElementById('tenant-login-recaptcha-token');
                    const siteKey = @json(config('services.recaptcha.site_key'));
                    if (!form || !tokenInput) return;

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        tokenInput.value = '';

                        function obtainTokenAndPost() {
                            grecaptcha.ready(function () {
                                grecaptcha.execute(siteKey, { action: 'tenant_login' })
                                    .then(function (token) {
                                        tokenInput.value = token;
                                        form.submit();
                                    })
                                    .catch(function () {
                                        alert('Could not verify reCAPTCHA. Check your connection, refresh the page, and try again.');
                                    });
                            });
                        }

                        function waitForApi(remaining) {
                            if (window.grecaptcha && typeof grecaptcha.ready === 'function') {
                                obtainTokenAndPost();
                                return;
                            }
                            if (remaining <= 0) {
                                alert('reCAPTCHA is still loading. Please wait a few seconds and click Sign in again.');
                                return;
                            }
                            setTimeout(function () { waitForApi(remaining - 1); }, 100);
                        }

                        waitForApi(120);
                    });
                })();
            </script>
        @endpush
    @endif

</x-tenant-guest-layout>

@endif
