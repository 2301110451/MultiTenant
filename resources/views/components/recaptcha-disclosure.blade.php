@props([
    'class' => '',
    'force' => false,
])

@php
    $recaptchaOn = (bool) config('services.recaptcha.enabled')
        && (string) config('services.recaptcha.site_key') !== ''
        && (string) config('services.recaptcha.secret_key') !== '';
    $isAuthenticated = auth('web')->check() || auth('tenant')->check();
@endphp

@if ($recaptchaOn && (! $isAuthenticated || $force))
    <div {{ $attributes->merge(['class' => 'recaptcha-hover-dock '.$class]) }}>
        <div class="recaptcha-hover-badge" role="note" aria-label="This site is protected by reCAPTCHA">
            <div class="recaptcha-hover-logo">
                {{-- Official asset: path is api2 (not api/v2) — wrong path 404s and shows a broken image --}}
                <img
                    src="https://www.gstatic.com/recaptcha/api2/logo_48.png"
                    width="36"
                    height="36"
                    alt="reCAPTCHA"
                    class="h-9 w-9 shrink-0"
                    loading="eager"
                    decoding="async"
                />
            </div>

            <div class="recaptcha-hover-content">
                <p class="recaptcha-hover-title">protected by reCAPTCHA</p>
                <p class="recaptcha-hover-links">
                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Privacy</a>
                        <span>&middot;</span>
                    <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">Terms</a>
                </p>
            </div>
        </div>
    </div>
    <script>
        document.body.classList.add('tenant-auth-recaptcha');
    </script>
@endif
