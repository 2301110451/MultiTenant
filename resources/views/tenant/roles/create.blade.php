<x-tenant-layout title="Create Role" breadcrumb="Create Role">
    <div class="px-6 py-8 sm:px-10 max-w-3xl">
        <form method="POST" action="{{ route('tenant.roles.store') }}" class="t-card p-6 space-y-4">
            @csrf
            <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Create Role</h1>

            <div>
                <label class="t-label">Role name</label>
                <input name="name" value="{{ old('name') }}" class="t-input" required>
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

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('tenant.roles.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold">Cancel</a>
                <button type="submit" class="t-btn-primary">Create Role</button>
            </div>
        </form>
    </div>
</x-tenant-layout>
