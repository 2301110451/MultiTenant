<x-tenant-layout title="My display" breadcrumb="My display">
    <div class="px-6 py-8 sm:px-10 max-w-xl space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">{{ session('status') }}</div>
        @endif
        <div class="t-card p-6 space-y-4">
            <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Database needs a quick update</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Personal display colors are stored on your user record. This tenant’s database has not received that update yet, so saving would fail.
            </p>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Run the following once on the server (from the project root). It updates <strong class="font-medium text-slate-800 dark:text-slate-200">every</strong> tenant database safely:
            </p>
            <pre class="text-xs sm:text-sm font-mono bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 p-4 rounded-xl border border-slate-200 dark:border-slate-700 overflow-x-auto">php artisan system:sync-tenant-migrations</pre>
            <p class="text-xs text-slate-500 dark:text-slate-500">Then refresh this page. If you are not the server administrator, send them this message.</p>
        </div>
    </div>
</x-tenant-layout>
