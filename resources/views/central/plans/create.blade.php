<x-central-layout title="Create Plan" breadcrumb="Create Plan">

    <div class="px-6 py-8 sm:px-10 max-w-2xl">

        <a href="{{ route('central.plans.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 font-medium mb-6 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Plans
        </a>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl card-shadow overflow-hidden">

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

            <div class="px-7 py-7">
                @include('central.plans._create_form', ['modal' => false])
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.querySelectorAll('[name^="feature_"]').forEach(input => {
        const track = input.nextElementSibling;
        if (!track) return;
        const thumb = track.querySelector('div');
        if (!thumb) return;
        const sync  = () => { thumb.style.transform = input.checked ? 'translateX(20px)' : ''; track.classList.toggle('bg-indigo-600', input.checked); track.classList.toggle('bg-slate-300', !input.checked); };
        sync();
        track.addEventListener('click', () => { input.checked = !input.checked; sync(); });
    });
    </script>
    @endpush

</x-central-layout>
