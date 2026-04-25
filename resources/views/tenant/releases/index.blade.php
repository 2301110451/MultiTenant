<x-tenant-layout title="Releases" breadcrumb="Releases">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Available Releases</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Read release notes and apply updates manually when your team is ready.
            </p>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/60 dark:bg-rose-900/20 dark:text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="t-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-left">Version</th>
                        <th class="px-4 py-3 text-left">Risk</th>
                        <th class="px-4 py-3 text-left">Detected Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($releases as $release)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $release->version ?? $release->suggested_version }}</td>
                            <td class="px-4 py-3 capitalize">{{ $release->risk_level }}</td>
                            <td class="px-4 py-3">{{ implode(', ', $release->changes_detected ?? []) ?: 'none' }}</td>
                        </tr>
                        <tr class="bg-slate-50/40">
                            <td colspan="3" class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                <strong>Release notes:</strong> {{ $release->notes }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-slate-500">No release updates available right now.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">{{ $releases->links() }}</div>
        </div>
    </div>
</x-tenant-layout>
