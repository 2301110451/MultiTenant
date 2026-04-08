<x-tenant-layout title="Reports" breadcrumb="Reports">

    <div class="px-6 py-8 sm:px-10">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Reports &amp; Analytics</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Included with your subscription when the plan supports analytics.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="t-card p-6">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                    Most Reserved Facilities
                </h3>
                <ul class="space-y-2 text-sm">
                    @forelse($topFacilities as $f)
                        <li class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                            <span>{{ $f->name }}</span>
                            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 px-2 py-0.5 rounded-full">{{ $f->reservations_count }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400 dark:text-slate-500">No data yet</li>
                    @endforelse
                </ul>
            </div>

            <div class="t-card p-6">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                    </svg>
                    Peak Days
                </h3>
                <ul class="space-y-2 text-sm">
                    @forelse($peakDays as $row)
                        <li class="flex items-center justify-between text-slate-700 dark:text-slate-300">
                            <span>{{ $row->day_name }}</span>
                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 px-2 py-0.5 rounded-full">{{ $row->total }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400 dark:text-slate-500">No data yet</li>
                    @endforelse
                </ul>
            </div>

            <div class="t-card p-6">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                    Revenue (paid payments)
                </h3>
                <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                    &#8369;{{ number_format($revenue ?? 0, 2) }}
                </p>
            </div>

            <div class="t-card p-6">
                <h3 class="font-bold text-slate-900 dark:text-slate-100 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    Damage Reports Logged
                </h3>
                <p class="text-3xl font-extrabold text-red-600 dark:text-red-400 mt-2">{{ $damageCount }}</p>
            </div>
        </div>
    </div>

</x-tenant-layout>
