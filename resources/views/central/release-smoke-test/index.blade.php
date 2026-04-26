<x-central-layout title="Release Smoke Test" breadcrumb="Release Smoke Test">
    <div class="px-6 py-8 sm:px-10">
        <div class="c-card p-6 space-y-2">
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                This page is for validating Central Releases detection flow.
            </p>
            <p class="text-xs font-semibold text-emerald-600">Status: {{ $status }}</p>
        </div>
    </div>
</x-central-layout>
