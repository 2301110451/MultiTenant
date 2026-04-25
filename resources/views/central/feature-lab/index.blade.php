<x-central-layout title="Feature Lab" breadcrumb="Feature Lab">
    <div class="px-6 py-8 sm:px-10">
        <div class="c-card p-6 space-y-3">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $featureName }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                This is a test-only admin feature page for release detection.
            </p>
            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                Status: {{ $status }}
            </span>
        </div>
    </div>
</x-central-layout>
