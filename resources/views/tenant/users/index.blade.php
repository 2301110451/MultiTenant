<x-tenant-layout title="User Management" breadcrumb="Users">
    @php
        $modal = $modal ?? '';
        $isCreateModalOpen = $canCreate && $modal === 'create-user';
        $isEditModalOpen = $editUser !== null && $modal === 'edit-user';
        $openCreateModalUrl = route('tenant.users.index', ['modal' => 'create-user']);
        $closeModalUrl = route('tenant.users.index');
    @endphp
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
            @if($canCreate)
                <a href="{{ $openCreateModalUrl }}" class="inline-flex items-center t-btn-primary">
                    Add User
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
                                <a href="{{ route('tenant.users.index', ['modal' => 'edit-user', 'user' => $user->id]) }}" class="t-link text-xs">Edit</a>
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

    @if($canCreate)
        <x-modal name="create-user-modal" :show="$isCreateModalOpen" maxWidth="2xl">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Create tenant user</h2>
                    <a href="{{ $closeModalUrl }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Close</a>
                </div>
                <form method="POST" action="{{ route('tenant.users.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_modal_context" value="create-user">

                    @if ($isCreateModalOpen && $errors->any())
                        <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="t-label" for="user_create_name">Name</label>
                        <input id="user_create_name" name="name" value="{{ old('name') }}" class="t-input" required>
                    </div>
                    <div>
                        <label class="t-label" for="user_create_email">Email</label>
                        <input id="user_create_email" type="email" name="email" value="{{ old('email') }}" class="t-input" required>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="t-label" for="user_create_password">Password</label>
                            <input id="user_create_password" type="password" name="password" class="t-input" required>
                        </div>
                        <div>
                            <label class="t-label" for="user_create_password_confirmation">Confirm Password</label>
                            <input id="user_create_password_confirmation" type="password" name="password_confirmation" class="t-input" required>
                        </div>
                    </div>
                    <div>
                        <label class="t-label" for="user_create_role_id">Role</label>
                        <select id="user_create_role_id" name="role_id" class="t-input" required>
                            <option value="">Select role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')>
                        Active account
                    </label>
                    <button type="submit" class="t-btn-primary w-full justify-center">Create User</button>
                </form>
            </div>
        </x-modal>
    @endif

    @if($editUser)
        <x-modal name="edit-user-modal" :show="$isEditModalOpen" maxWidth="2xl">
            <div class="p-6 sm:p-8 space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Edit tenant user</h2>
                    <a href="{{ $closeModalUrl }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Close</a>
                </div>
                <form method="POST" action="{{ route('tenant.users.update', $editUser) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal_context" value="edit-user">
                    <input type="hidden" name="_modal_target_id" value="{{ $editUser->id }}">

                    @if ($isEditModalOpen && $errors->any())
                        <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="t-label" for="user_edit_name">Name</label>
                        <input id="user_edit_name" name="name" value="{{ old('name', $editUser->name) }}" class="t-input" required>
                    </div>
                    <div>
                        <label class="t-label" for="user_edit_email">Email</label>
                        <input id="user_edit_email" type="email" name="email" value="{{ old('email', $editUser->email) }}" class="t-input" required>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="t-label" for="user_edit_password">New Password (optional)</label>
                            <input id="user_edit_password" type="password" name="password" class="t-input">
                        </div>
                        <div>
                            <label class="t-label" for="user_edit_password_confirmation">Confirm Password</label>
                            <input id="user_edit_password_confirmation" type="password" name="password_confirmation" class="t-input">
                        </div>
                    </div>
                    <div>
                        <label class="t-label" for="user_edit_role_id">Role</label>
                        @php $currentRoleId = old('role_id', $editUser->roles->first()?->id); @endphp
                        <select id="user_edit_role_id" name="role_id" class="t-input" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) $currentRoleId === (string) $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked((string) old('is_active', $editUser->is_active ? '1' : '0') === '1')>
                        Active account
                    </label>
                    <button type="submit" class="t-btn-primary w-full justify-center">Save Changes</button>
                </form>
                <form method="POST" action="{{ route('tenant.users.destroy', $editUser) }}" onsubmit="return confirm('Delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs font-semibold text-red-600 hover:text-red-800">Delete User</button>
                </form>
            </div>
        </x-modal>
    @endif
</x-tenant-layout>
