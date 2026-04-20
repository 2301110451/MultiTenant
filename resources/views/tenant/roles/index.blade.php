<x-tenant-layout title="Role Management" breadcrumb="Roles">
    <div class="px-6 py-8 sm:px-10 space-y-6" data-live-endpoint="{{ route('tenant.realtime.roles') }}" data-live-interval="12000">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Roles & Permissions</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Manage tenant roles and permission assignments.
                    <span class="ml-2">Total roles: <strong data-live-key="totalRoles">{{ $roles->total() }}</strong></span>
                    <span class="ml-2">Custom roles: <strong data-live-key="customRoles">{{ $roles->getCollection()->whereNotIn('name', ['tenant_admin', 'staff', 'viewer', 'resident'])->count() }}</strong></span>
                </p>
            </div>
            <a href="{{ route('tenant.roles.create') }}" class="inline-flex items-center t-btn-primary">
                Add Role
            </a>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <div class="t-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left px-4 py-3">Role</th>
                        <th class="text-left px-4 py-3">Permissions</th>
                        <th class="text-right px-4 py-3">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($roles as $role)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $role->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $role->permissions->pluck('name')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tenant.roles.edit', $role) }}" class="t-link text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-slate-500">No roles found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">{{ $roles->links() }}</div>
        </div>
    </div>
</x-tenant-layout>
