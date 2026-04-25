<x-tenant-layout title="Role Management" breadcrumb="Roles">
    @php
        $modal = $modal ?? '';
        $isCreateModalOpen = $canCreate && $modal === 'create-role';
        $isEditModalOpen = $editRole !== null && $modal === 'edit-role';
        $openCreateModalUrl = route('tenant.roles.index', ['modal' => 'create-role']);
        $closeModalUrl = route('tenant.roles.index');
    @endphp
    <div class="px-6 py-8 sm:px-10 space-y-6" data-live-endpoint="{{ route('tenant.realtime.roles') }}" data-live-interval="12000">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Roles & Permissions</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Manage tenant roles and permission assignments.
                    <span class="ml-2">Total roles: <strong data-live-key="totalRoles">{{ $roles->total() }}</strong></span>
                    <span class="ml-2">Custom roles: <strong data-live-key="customRoles">{{ $roles->getCollection()->whereNotIn('name', ['tenant_admin', 'staff', 'resident'])->count() }}</strong></span>
                </p>
            </div>
            @if($canCreate)
                <a href="{{ $openCreateModalUrl }}" class="inline-flex items-center t-btn-primary">
                    Add Role
                </a>
            @endif
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
                                <a href="{{ route('tenant.roles.index', ['modal' => 'edit-role', 'role' => $role->id]) }}" class="t-link text-xs">Edit</a>
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

    @if($canCreate)
        <x-modal name="create-role-modal" :show="$isCreateModalOpen" maxWidth="2xl">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Create role</h2>
                    <a href="{{ $closeModalUrl }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Close</a>
                </div>
                <form method="POST" action="{{ route('tenant.roles.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_modal_context" value="create-role">

                    @if ($isCreateModalOpen && $errors->any())
                        <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="t-label" for="role_create_name">Role name</label>
                        <input id="role_create_name" name="name" value="{{ old('name') }}" class="t-input" required>
                    </div>

                    <div>
                        <label class="t-label">Permissions</label>
                        <div class="grid sm:grid-cols-2 gap-2 rounded-xl border border-slate-200 dark:border-slate-700 p-3">
                            @foreach($permissions as $permission)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array((string) $permission->id, collect(old('permissions', []))->map(fn($v)=>(string)$v)->all(), true))>
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="t-btn-primary w-full justify-center">Create Role</button>
                </form>
            </div>
        </x-modal>
    @endif

    @if($editRole)
        <x-modal name="edit-role-modal" :show="$isEditModalOpen" maxWidth="2xl">
            <div class="p-6 sm:p-8 space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Edit role</h2>
                    <a href="{{ $closeModalUrl }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Close</a>
                </div>
                <form method="POST" action="{{ route('tenant.roles.update', $editRole) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal_context" value="edit-role">
                    <input type="hidden" name="_modal_target_id" value="{{ $editRole->id }}">

                    @if ($isEditModalOpen && $errors->any())
                        <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="t-label" for="role_edit_name">Role name</label>
                        <input id="role_edit_name" name="name" value="{{ old('name', $editRole->name) }}" class="t-input" required>
                    </div>

                    <div>
                        <label class="t-label">Permissions</label>
                        @php
                            $selected = collect(old('permissions', $editRole->permissions->pluck('id')->all()))
                                ->map(fn($v) => (string) $v)->all();
                        @endphp
                        <div class="grid sm:grid-cols-2 gap-2 rounded-xl border border-slate-200 dark:border-slate-700 p-3">
                            @foreach($permissions as $permission)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array((string) $permission->id, $selected, true))>
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="t-btn-primary w-full justify-center">Save Role</button>
                </form>

                <form method="POST" action="{{ route('tenant.roles.destroy', $editRole) }}" onsubmit="return confirm('Delete this role?');">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs font-semibold text-red-600 hover:text-red-800">Delete Role</button>
                </form>
            </div>
        </x-modal>
    @endif
</x-tenant-layout>
