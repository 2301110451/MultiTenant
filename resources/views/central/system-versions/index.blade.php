<x-central-layout title="System Versions" breadcrumb="System Versions">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">System Versions</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 max-w-3xl">
            Log <strong class="font-medium text-slate-800 dark:text-slate-200">deployed release metadata</strong> for operators (version label, type, notes). This is not Git or a deployment pipeline—use your normal source control and hosting workflow for code history and rollouts.
        </p>
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('central.system-versions.store') }}" class="c-card p-6 space-y-4">
            @csrf
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Log New Release</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <input name="version" placeholder="v1.0.0" class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300">
                <select name="release_type" class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300">
                    <option>feature</option><option>security</option><option>hotfix</option><option>maintenance</option>
                </select>
            </div>
            <input name="migration_batch" placeholder="batch_2026_04_09" class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300">
            <textarea name="notes" rows="3" class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300" placeholder="Release notes"></textarea>
            <div class="flex justify-end"><button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Save</button></div>
        </form>

        <div class="c-card overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 border-b border-slate-200"><th class="px-4 py-3 text-left">Version</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Released</th><th class="px-4 py-3 text-left">Notes</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($versions as $v)
                    <tr><td class="px-4 py-3 font-semibold">{{ $v->version }}</td><td class="px-4 py-3">{{ $v->release_type }}</td><td class="px-4 py-3">{{ $v->released_at?->format('M d, Y H:i') ?? '—' }}</td><td class="px-4 py-3">{{ \Illuminate\Support\Str::limit((string) $v->notes, 120) }}</td></tr>
                @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">{{ $versions->links() }}</div>
        </div>
    </div>
</x-central-layout>
