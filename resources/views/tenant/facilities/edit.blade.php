@php $tb = \App\Support\TenantAppearance::theme()['button']; @endphp
<x-tenant-layout title="Edit facility" breadcrumb="Edit facility">

    <div class="px-6 py-8 sm:px-10 max-w-xl space-y-6">
        <form method="POST" action="{{ route('tenant.facilities.update', $facility) }}" class="t-card p-6 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="t-label" for="name">Name</label>
                <input id="name" name="name" class="t-input" value="{{ old('name', $facility->name) }}" required />
            </div>
            <div>
                <label class="t-label" for="description">Description</label>
                <textarea id="description" name="description" rows="3" class="t-textarea">{{ old('description', $facility->description) }}</textarea>
            </div>
            <div>
                <label class="t-label" for="capacity">Capacity</label>
                <input id="capacity" name="capacity" type="number" min="0" class="t-input" value="{{ old('capacity', $facility->capacity) }}" required />
            </div>
            <div>
                <label class="t-label" for="rules">Rules</label>
                <textarea id="rules" name="rules" rows="3" class="t-textarea">{{ old('rules', $facility->rules) }}</textarea>
            </div>
            <div>
                <label class="t-label" for="hourly_rate">Hourly rate</label>
                <input id="hourly_rate" name="hourly_rate" type="number" step="0.01" min="0" class="t-input" value="{{ old('hourly_rate', $facility->hourly_rate) }}" />
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $facility->is_active))
                       class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 dark:bg-slate-800" />
                <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
            </label>
            <button type="submit" class="w-full py-3 rounded-xl text-white text-sm font-semibold shadow-sm transition {{ $tb }}">
                Save changes
            </button>
        </form>

        <form method="POST" action="{{ route('tenant.facilities.destroy', $facility) }}"
              class="t-card p-6 border-red-200 dark:border-red-900"
              onsubmit="return confirm('Delete this facility? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Permanently remove this facility and all its reservation records.</p>
            <button type="submit" class="text-sm font-semibold text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                Delete facility
            </button>
        </form>
    </div>

</x-tenant-layout>
