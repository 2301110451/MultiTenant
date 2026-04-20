<x-tenant-layout title="User Management" breadcrumb="Users">
    <div class="px-6 py-8 sm:px-10 space-y-6" data-live-endpoint="{{ route('tenant.realtime.users') }}" data-live-interval="10000">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Tenant Users</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Manage user accounts and role access within this tenant.
                    <span class="ml-2">Total: <strong data-live-key="totalUsers">{{ $users->total() }}</strong></span>
                    <span class="ml-2">Active: <strong data-live-key="activeUsers">{{ $users->getCollection()->where('is_active', true)->count() }}</strong></span>
                    <span class="ml-2">Inactive: <strong data-live-key="inactiveUsers">{{ $users->getCollection()->where('is_active', false)->count() }}</strong></span>
                </p>
            </div>
            <a href="{{ route('tenant.users.create') }}" class="inline-flex items-center t-btn-primary">
                Add User
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
                        <th class="text-left px-4 py-3">Name</th>
                        <th class="text-left px-4 py-3">Email</th>
                        <th class="text-left px-4 py-3">Role</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: ucfirst((string) $user->role->value) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tenant.users.edit', $user) }}" class="t-link text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No users found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">{{ $users->links() }}</div>
        </div>
    </div>
</x-tenant-layout>
