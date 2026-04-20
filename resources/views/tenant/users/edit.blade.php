<x-tenant-layout title="Edit User" breadcrumb="Edit User">
    <div class="px-6 py-8 sm:px-10 max-w-2xl space-y-4">
        <form method="POST" action="{{ route('tenant.users.update', $user) }}" class="t-card p-6 space-y-4">
            @csrf
            @method('PUT')
            <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Edit Tenant User</h1>

            <div>
                <label class="t-label">Name</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="t-input" required>
            </div>
            <div>
                <label class="t-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="t-input" required>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="t-label">New Password (optional)</label>
                    <input type="password" name="password" class="t-input">
                </div>
                <div>
                    <label class="t-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="t-input">
                </div>
            </div>
            <div>
                <label class="t-label">Role</label>
                @php $currentRoleId = old('role_id', $user->roles->first()?->id); @endphp
                <select name="role_id" class="t-input" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) $currentRoleId === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked((string) old('is_active', $user->is_active ? '1' : '0') === '1')>
                Active account
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('tenant.users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold">Cancel</a>
                <button type="submit" class="t-btn-primary">Save Changes</button>
            </div>
        </form>

        <form method="POST" action="{{ route('tenant.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
            @csrf
            @method('DELETE')
            <button class="text-xs font-semibold text-red-600 hover:text-red-800">Delete User</button>
        </form>
    </div>
</x-tenant-layout>
