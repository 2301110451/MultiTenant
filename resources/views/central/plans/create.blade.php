<x-central-layout title="Create Plan" breadcrumb="Create Plan">

    <div class="px-6 py-8 sm:px-10 max-w-2xl">

        <a href="{{ route('central.plans.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 font-medium mb-6 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Plans
        </a>

        <div class="bg-white border border-slate-200 rounded-2xl card-shadow overflow-hidden">

            {{-- card header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-7 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">New Subscription Plan</h2>
                        <p class="text-indigo-200 text-sm">Define limits and feature access for this tier.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('central.plans.store') }}" class="px-7 py-7 space-y-6">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="name">
                        Plan Name <span class="text-red-500">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                           placeholder="e.g. Premium"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                                  {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                    @error('name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="slug">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input id="slug" name="slug" type="text" value="{{ old('slug') }}" required
                           placeholder="e.g. premium"
                           class="w-full px-4 py-2.5 text-sm font-mono border rounded-xl transition
                                  {{ $errors->has('slug') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                    @error('slug')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-400">Lowercase, no spaces. Used internally and for plan matching.</p>
                </div>

                {{-- Reservation limit --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="monthly_reservation_limit">
                        Monthly Reservation Limit
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                        </div>
                        <input id="monthly_reservation_limit" name="monthly_reservation_limit" type="number" min="1"
                               value="{{ old('monthly_reservation_limit') }}"
                               placeholder="Leave blank for unlimited"
                               class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    @error('monthly_reservation_limit')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Feature toggles --}}
                <div>
                    <p class="text-sm font-semibold text-slate-700 mb-3">Features</p>
                    <div class="space-y-3 bg-slate-50 rounded-xl p-4 border border-slate-200">
                        @php
                        $featureOptions = [
                            'reports'  => ['Reports & Analytics', 'Generate in-depth reservation and revenue reports.'],
                            'qr'       => ['QR Check-in / Check-out', 'Allow residents to check in via QR code scan.'],
                            'payments' => ['Payment Integration', 'Accept online payments through integrated gateways.'],
                        ];
                        @endphp
                        @foreach($featureOptions as $key => [$label, $desc])
                        <label class="flex items-start gap-3 cursor-pointer group p-2 rounded-lg hover:bg-white transition">
                            <div class="relative flex items-center pt-0.5">
                                <input type="checkbox" name="feature_{{ $key }}" value="1"
                                       {{ old('feature_'.$key) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-10 h-5 rounded-full bg-slate-300 peer-checked:bg-indigo-600 transition-colors relative cursor-pointer">
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5 duration-200"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
                                <p class="text-xs text-slate-400">{{ $desc }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Checked features will be stored in the <code class="bg-slate-100 px-1 rounded">features</code> JSON column.</p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-600/25 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Create Plan
                    </button>
                    <a href="{{ route('central.plans.index') }}"
                       class="px-5 py-3 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    // wire the custom toggle divs to the hidden checkboxes
    document.querySelectorAll('[name^="feature_"]').forEach(input => {
        const track = input.nextElementSibling;
        const thumb = track.querySelector('div');
        const sync  = () => { thumb.style.transform = input.checked ? 'translateX(20px)' : ''; track.classList.toggle('bg-indigo-600', input.checked); track.classList.toggle('bg-slate-300', !input.checked); };
        sync();
        track.addEventListener('click', () => { input.checked = !input.checked; sync(); });
    });
    </script>
    @endpush

</x-central-layout>
