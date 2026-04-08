<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request received — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100 antialiased">
    <div class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-4 py-12">
        <div class="w-full rounded-2xl border border-emerald-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Request received</h1>
            @if (session('tenant_name'))
                <p class="mt-2 text-sm text-slate-600">
                    Thank you. Your choice for <strong>{{ session('tenant_name') }}</strong> has been recorded. The central team will follow up.
                </p>
            @else
                <p class="mt-2 text-sm text-slate-600">Thank you. Your choice has been recorded.</p>
            @endif
        </div>
        <a href="{{ route('home') }}" class="mt-8 text-sm font-medium text-indigo-600 hover:text-indigo-800">Back to home</a>
    </div>
</body>
</html>
