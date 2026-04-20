<x-central-layout title="Update Announcements" breadcrumb="Update Announcements">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Update Announcements</h1>
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('central.update-announcements.store') }}" class="c-card p-6 space-y-4">
            @csrf
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Publish Update</h2>
            <input name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300" placeholder="Title">
            @error('title')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <textarea name="message" rows="3" class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300" placeholder="Announcement message">{{ old('message') }}</textarea>
            @error('message')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <div class="grid sm:grid-cols-2 gap-4">
                <select name="target_tenant_id" class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300">
                    <option value="">All barangays/tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected((string) old('target_tenant_id') === (string) $tenant->id)>
                            {{ $tenant->name }}
                        </option>
                    @endforeach
                </select>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Active
                </label>
            </div>
            <div class="space-y-2">
                @error('target_tenant_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Choose one barangay/tenant to target, or leave as all.
                </p>
            </div>
            <div class="flex justify-end"><button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Publish</button></div>
        </form>

        <div class="c-card overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 border-b border-slate-200"><th class="px-4 py-3 text-left">Title</th><th class="px-4 py-3 text-left">Target</th><th class="px-4 py-3 text-left">Active</th><th class="px-4 py-3 text-left">Published</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($updates as $u)
                    <tr>
                        <td class="px-4 py-3">{{ $u->title }}</td>
                        <td class="px-4 py-3">
                            @if(empty($u->targeted_tenant_ids))
                                All barangays/tenants
                            @else
                                @php
                                    $targetId = (int) ($u->targeted_tenant_ids[0] ?? 0);
                                    $targetTenant = $tenants->firstWhere('id', $targetId);
                                @endphp
                                {{ $targetTenant?->name ?? ('Tenant #'.$targetId) }}
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $u->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3">{{ $u->published_at?->format('M d, Y H:i') ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-slate-100">{{ $updates->links() }}</div>
        </div>
    </div>
</x-central-layout>
