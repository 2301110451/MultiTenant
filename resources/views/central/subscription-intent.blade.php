@php
    $subscription = $tenant->subscription;
    $effectivePlan = $subscription?->plan ?? $tenant->plan;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Subscription choice — {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100 antialiased">
    <div class="mx-auto max-w-lg px-4 py-12 sm:py-16">
        <div class="mb-8 text-center">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">Suspended portal</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $tenant->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">Choose how you want to proceed with your subscription.</p>
        </div>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-800">On record</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Plan</dt>
                    <dd class="font-medium text-slate-900">{{ $effectivePlan?->name ?? '—' }}</dd>
                </div>
                @if($subscription)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Subscription</dt>
                        <dd class="font-medium text-slate-900 capitalize">{{ $subscription->status }}</dd>
                    </div>
                    @if($subscription->ends_at)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Period end</dt>
                            <dd class="font-medium text-slate-900">{{ $subscription->ends_at->timezone(config('app.timezone'))->format('M j, Y') }}</dd>
                        </div>
                    @endif
                @endif
            </dl>
        </div>

        <form method="post" action="{{ $postUrl }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <fieldset>
                <legend class="text-sm font-semibold text-slate-900">Your decision</legend>
                <p class="mt-1 text-xs text-slate-500">This request is recorded for the central administrator. It does not automatically change billing or portal status.</p>

                <div class="mt-4 space-y-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 hover:border-indigo-300">
                        <input type="radio" name="intent" value="unsubscribe" class="mt-1 text-indigo-600" {{ old('intent') === 'unsubscribe' ? 'checked' : '' }} required>
                        <span>
                            <span class="font-medium text-slate-900">Unsubscribe completely</span>
                            <span class="mt-0.5 block text-xs text-slate-600">Request to end the barangay tenant subscription and related services.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 hover:border-indigo-300">
                        <input type="radio" name="intent" value="extend" class="mt-1 text-indigo-600" {{ old('intent') === 'extend' ? 'checked' : '' }}>
                        <span>
                            <span class="font-medium text-slate-900">Request extension</span>
                            <span class="mt-0.5 block text-xs text-slate-600">Ask to extend the current subscription period or discuss renewal.</span>
                        </span>
                    </label>
                </div>
                @error('intent')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            </fieldset>

            <div class="mt-6">
                <label for="message" class="block text-sm font-medium text-slate-700">Message <span class="font-normal text-slate-500">(optional)</span></label>
                <textarea id="message" name="message" rows="4" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm" placeholder="Notes for the central team…">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn-primary mt-6 w-full justify-center py-3">
                Submit choice
            </button>
        </form>

        <p class="mt-8 text-center text-xs text-slate-500">
            Link expires 30 days after it was issued. For help, contact your central administrator.
        </p>
    </div>
</body>
</html>
