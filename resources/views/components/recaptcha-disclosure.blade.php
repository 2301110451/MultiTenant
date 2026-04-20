@props([
    'class' => '',
])

@php
    $recaptchaOn = (bool) config('services.recaptcha.enabled')
        && (string) config('services.recaptcha.site_key') !== ''
        && (string) config('services.recaptcha.secret_key') !== '';
@endphp

@if ($recaptchaOn)
    <div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-2 pt-1 '.$class]) }}>
        {{-- Official asset: path is api2 (not api/v2) — wrong path 404s and shows a broken image --}}
        <img
            src="https://www.gstatic.com/recaptcha/api2/logo_48.png"
            width="48"
            height="48"
            alt="reCAPTCHA"
            class="h-12 w-12 shrink-0 opacity-95 dark:opacity-90"
            loading="eager"
            decoding="async"
        />
        <p class="text-center text-[11px] leading-relaxed text-slate-500 dark:text-slate-400 max-w-sm">
            This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="underline decoration-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Privacy Policy</a>
            and
            <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer" class="underline decoration-slate-400 hover:text-slate-700 dark:hover:text-slate-300">Terms of Service</a>
            apply.
        </p>
    </div>
    <script>
        document.body.classList.add('tenant-auth-recaptcha');
    </script>
@endif
