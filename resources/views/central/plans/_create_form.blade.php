@php
    $isModal = ! empty($modal);
    $idPrefix = $isModal ? 'modal_plan_' : '';
@endphp

<form method="POST" action="{{ route('central.plans.store') }}" class="space-y-6">
    @csrf
    @if($isModal)
        <input type="hidden" name="plan_create_modal" value="1">
    @endif

    {{-- Name --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $idPrefix }}name">
            Plan Name <span class="text-red-500">*</span>
        </label>
        <input id="{{ $idPrefix }}name" name="name" type="text" value="{{ old('name') }}" required
               placeholder="e.g. Premium"
               class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                      {{ $errors->has('name') ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/40' }}">
        @error('name')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    {{-- Slug --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $idPrefix }}slug">
            Slug <span class="text-red-500">*</span>
        </label>
        <input id="{{ $idPrefix }}slug" name="slug" type="text" value="{{ old('slug') }}" required
               placeholder="e.g. premium"
               class="w-full px-4 py-2.5 text-sm font-mono border rounded-xl transition
                      {{ $errors->has('slug') ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/40' }}">
        @error('slug')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Lowercase, no spaces. Used internally and for plan matching.</p>
    </div>

    {{-- Reservation limit --}}
    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $idPrefix }}monthly_reservation_limit">
            Monthly Reservation Limit
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
            </div>
            <input id="{{ $idPrefix }}monthly_reservation_limit" name="monthly_reservation_limit" type="number" min="1"
                   value="{{ old('monthly_reservation_limit') }}"
                   placeholder="Leave blank for unlimited"
                   class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/40">
        </div>
        @error('monthly_reservation_limit')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    {{-- Feature toggles --}}
    <div>
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Features</p>
        <div class="space-y-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            @php
            $featureOptions = [
                'reports'  => ['Reports & Analytics', 'Generate in-depth reservation and revenue reports.'],
                'qr'       => ['QR Check-in / Check-out', 'Allow residents to check in via QR code scan.'],
                'payments' => ['Payment Integration', 'Accept online payments through integrated gateways.'],
            ];
            @endphp
            @foreach($featureOptions as $key => [$label, $desc])
            <label class="flex items-start gap-3 cursor-pointer group p-2 rounded-lg hover:bg-white dark:hover:bg-slate-800 transition">
                <div class="relative flex items-center pt-0.5">
                    <input type="checkbox" name="feature_{{ $key }}" value="1"
                           {{ old('feature_'.$key) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-10 h-5 rounded-full bg-slate-300 peer-checked:bg-indigo-600 transition-colors relative cursor-pointer">
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5 duration-200"></div>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $label }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $desc }}</p>
                </div>
            </label>
            @endforeach
        </div>
        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Checked features will be stored in the <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">features</code> JSON column.</p>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-700">
        <button type="submit"
                class="flex-1 flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-600/25 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Plan
        </button>
        @if($isModal)
            <button type="button" @click="showCreateModal = false"
                    class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                Cancel
            </button>
        @else
            <a href="{{ route('central.plans.index') }}"
               class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                Cancel
            </a>
        @endif
    </div>
</form>
