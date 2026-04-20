@php $domainSuffix = config('tenancy.tenant_domain_suffix'); @endphp
<x-central-layout title="Add Barangay" breadcrumb="Add Barangay">

    <div class="px-6 py-8 sm:px-10 max-w-3xl">

        <a href="{{ route('central.tenants.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 font-medium mb-6 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Barangays
        </a>

        <div class="c-card overflow-hidden">

            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-7 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Register New Barangay</h2>
                        <p class="text-indigo-200 text-sm">A tenant domain and separate database are created automatically from the name.</p>
                    </div>
                </div>
            </div>

            <div class="px-7 py-7">
                @include('central.tenants._form', ['modal' => false, 'plans' => $plans])
            </div>
        </div>
    </div>

</x-central-layout>
