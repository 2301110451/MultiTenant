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

    <form id="tenant-register-form" method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition {{ $errors->has('name') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900' }}">
            @error('name')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition {{ $errors->has('email') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900' }}">
            @error('email')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition {{ $errors->has('password') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900' }}">
            @error('password')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition">
        </div>

        @if($recaptchaEnabled)
            <div>
                <input type="hidden" name="g-recaptcha-response" id="tenant-register-recaptcha-token">
                @error('g-recaptcha-response')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl text-white text-sm font-semibold shadow-lg transition focus:outline-none focus:ring-4 focus:ring-indigo-200 {{ $tTheme['button'] }}">
            Register &amp; continue
        </button>

        @if($recaptchaEnabled)
            <x-recaptcha-disclosure class="mt-2" />
        @endif
    </form>

    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold {{ $tTheme['breadcrumbAccent'] }} hover:underline">Sign in</a>
    </p>

    @if($recaptchaEnabled)
        @push('scripts')
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}" async defer></script>
            <script>
                (function () {
                    const form = document.getElementById('tenant-register-form');
                    const tokenInput = document.getElementById('tenant-register-recaptcha-token');
                    const siteKey = @json(config('services.recaptcha.site_key'));
                    if (!form || !tokenInput) return;

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        tokenInput.value = '';

                        function obtainTokenAndPost() {
                            grecaptcha.ready(function () {
                                grecaptcha.execute(siteKey, { action: 'tenant_register' })
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
                                alert('reCAPTCHA is still loading. Please wait a few seconds and try again.');
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
