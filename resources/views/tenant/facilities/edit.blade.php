@php
    use App\Enums\FacilityKind;
@endphp
<x-tenant-layout title="Edit listing" breadcrumb="Edit listing">

    <div class="px-6 py-8 sm:px-10 max-w-xl space-y-6">
        <form method="POST" action="{{ route('tenant.facilities.update', $facility) }}" enctype="multipart/form-data" class="t-card p-6 sm:p-8 space-y-5">
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
                <label class="t-label" for="kind">Category</label>
                <select id="kind" name="kind" class="t-input" required>
                    <option value="{{ FacilityKind::Facility->value }}" @selected(old('kind', $facility->kind->value) === FacilityKind::Facility->value)>Facility</option>
                    <option value="{{ FacilityKind::Equipment->value }}" @selected(old('kind', $facility->kind->value) === FacilityKind::Equipment->value)>Equipment</option>
                </select>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Card icon: {{ $facility->kind->emoji() }} — changes when you switch category.</p>
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
            <div class="space-y-3">
                <label class="t-label" for="image">Listing photo</label>
                @if($facility->image_path)
                    <img src="{{ route('tenant.facilities.image', $facility) }}" alt="{{ $facility->name }} photo" class="w-full max-h-56 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
                @endif
                <input id="image" name="image" type="file" accept="image/*" class="t-input file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700" />
                <p class="text-xs text-slate-500 dark:text-slate-400">Upload a new image to replace the current one. PNG, JPG, WEBP up to 5MB.</p>
                @if($facility->image_path)
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="remove_image" value="1" class="t-checkbox-accent rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800">
                        Remove current photo
                    </label>
                @endif
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $facility->is_active))
                       class="t-checkbox-accent rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
            </label>
            <button type="submit" class="t-btn-primary w-full justify-center py-3 shadow-sm">
                Save changes
            </button>
        </form>

        <form method="POST" action="{{ route('tenant.facilities.destroy', $facility) }}"
              class="t-card p-6 border-red-200 dark:border-red-900"
              onsubmit="return confirm('Delete this facility? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">Permanently remove this listing and related reservation records.</p>
            <button type="submit" class="text-sm font-semibold text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                Delete listing
            </button>
        </form>
    </div>

</x-tenant-layout>
