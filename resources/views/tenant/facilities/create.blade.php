@php
    use App\Enums\FacilityKind;
@endphp
<x-tenant-layout title="New listing" breadcrumb="New listing">

    <div class="px-6 py-8 sm:px-10 max-w-xl">
        <form method="POST" action="{{ route('tenant.facilities.store') }}" enctype="multipart/form-data" class="t-card p-6 sm:p-8 space-y-5">
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
                <label class="t-label" for="kind">Category</label>
                <select id="kind" name="kind" class="t-input" required>
                    <option value="{{ FacilityKind::Facility->value }}" @selected(old('kind', FacilityKind::Facility->value) === FacilityKind::Facility->value)>Facility (hall, court, room, space)</option>
                    <option value="{{ FacilityKind::Equipment->value }}" @selected(old('kind') === FacilityKind::Equipment->value)>Equipment (rentable item by time slot)</option>
                </select>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Use <strong class="font-medium text-slate-600 dark:text-slate-300">Facility</strong> for spaces; use <strong class="font-medium text-slate-600 dark:text-slate-300">Equipment</strong> for lendable items people still reserve with a start and end time. The listing card shows <strong class="font-medium text-slate-600 dark:text-slate-300">{{ FacilityKind::Facility->emoji() }}</strong> or <strong class="font-medium text-slate-600 dark:text-slate-300">{{ FacilityKind::Equipment->emoji() }}</strong> automatically from this category.</p>
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
            <div>
                <label class="t-label" for="image">Listing photo (optional)</label>
                <input id="image" name="image" type="file" accept="image/*" class="t-input file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700" />
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">PNG, JPG, WEBP up to 5MB.</p>
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" checked
                       class="t-checkbox-accent rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
            </label>
            <button type="submit" class="t-btn-primary w-full justify-center py-3 shadow-sm">
                Save listing
            </button>
        </form>
    </div>

</x-tenant-layout>
