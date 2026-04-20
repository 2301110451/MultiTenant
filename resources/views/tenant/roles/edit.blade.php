<x-tenant-layout title="Edit Role" breadcrumb="Edit Role">
    <div class="px-6 py-8 sm:px-10 max-w-3xl space-y-4">
        <form method="POST" action="{{ route('tenant.roles.update', $role) }}" class="t-card p-6 space-y-4">
            @csrf
            @method('PUT')
            <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Edit Role</h1>

            <div>
                <label class="t-label">Role name</label>
                <input name="name" value="{{ old('name', $role->name) }}" class="t-input" required>
            </div>

            <div>
                <label class="t-label">Permissions</label>
                @php
                    $selected = collect(old('permissions', $role->permissions->pluck('id')->all()))
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

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('tenant.roles.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold">Cancel</a>
                <button type="submit" class="t-btn-primary">Save Role</button>
            </div>
        </form>

        <form method="POST" action="{{ route('tenant.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?');">
            @csrf
            @method('DELETE')
            <button class="text-xs font-semibold text-red-600 hover:text-red-800">Delete Role</button>
        </form>
    </div>
</x-tenant-layout>
