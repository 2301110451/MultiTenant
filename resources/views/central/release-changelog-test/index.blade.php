<x-central-layout title="Release Changelog Test" breadcrumb="Release Changelog Test">
    <div class="px-6 py-8 sm:px-10">
        <div class="c-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h1>
                <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {{ $version }}
                </span>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-400">
                Test page for validating Central Releases detection and review flow.
            </p>

            <ul class="list-disc pl-5 space-y-1 text-sm text-slate-700 dark:text-slate-300">
                @foreach($items as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</x-central-layout>
