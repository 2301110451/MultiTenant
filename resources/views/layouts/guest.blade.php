<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-neutral-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center bg-neutral-50 px-4 py-8">
            <div>
                <a href="/">
                    <x-application-logo class="h-16 w-16 fill-current text-primary-600" />
                </a>
            </div>

            <div class="surface-card mt-6 w-full max-w-md overflow-hidden px-6 py-6">
                {{ $slot }}
            </div>
        </div>
        <x-recaptcha-disclosure />
    </body>
</html>
