<x-tenant-layout title="Create User" breadcrumb="Create User">
    <div class="px-6 py-8 sm:px-10 max-w-2xl">
        <form method="POST" action="{{ route('tenant.users.store') }}" class="t-card p-6 space-y-4">
            @csrf
            <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Create Tenant User</h1>

            <div>
                <label class="t-label">Name</label>
                <input name="name" value="{{ old('name') }}" class="t-input" required>
            </div>
            <div>
                <label class="t-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="t-input" required>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="t-label">Password</label>
                    <input type="password" name="password" class="t-input" required>
                </div>
                <div>
                    <label class="t-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="t-input" required>
                </div>
            </div>
            <div>
                <label class="t-label">Role</label>
                <select name="role_id" class="t-input" required>
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

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('tenant.users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold">Cancel</a>
                <button type="submit" class="t-btn-primary">Create User</button>
            </div>
        </form>
    </div>
</x-tenant-layout>
