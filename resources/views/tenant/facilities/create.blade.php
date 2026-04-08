@php $tb = \App\Support\TenantAppearance::theme()['button']; @endphp
<x-tenant-layout title="New facility" breadcrumb="New facility">

    <div class="px-6 py-8 sm:px-10 max-w-xl">
        <form method="POST" action="{{ route('tenant.facilities.store') }}" class="t-card p-6 sm:p-8 space-y-5">
            @csrf

            @if ($errors->any())
                <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="t-label" for="name">Name</label>
                <input id="name" name="name" class="t-input" value="{{ old('name') }}" required />
            </div>
            <div>
                <label class="t-label" for="description">Description</label>
                <textarea id="description" name="description" rows="3" class="t-textarea">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="t-label" for="capacity">Capacity</label>
                <input id="capacity" name="capacity" type="number" min="0" class="t-input" value="{{ old('capacity', 0) }}" required />
            </div>
            <div>
                <label class="t-label" for="rules">Rules</label>
                <textarea id="rules" name="rules" rows="3" class="t-textarea">{{ old('rules') }}</textarea>
            </div>
            <div>
                <label class="t-label" for="hourly_rate">Hourly rate</label>
                <input id="hourly_rate" name="hourly_rate" type="number" step="0.01" min="0" class="t-input" value="{{ old('hourly_rate', 0) }}" />
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" checked
                       class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 dark:bg-slate-800" />
                <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
            </label>
            <button type="submit" class="w-full py-3 rounded-xl text-white text-sm font-semibold shadow-sm transition {{ $tb }}">
                Save facility
            </button>
        </form>
    </div>

</x-tenant-layout>
