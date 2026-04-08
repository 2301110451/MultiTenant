@php $domainSuffix = config('tenancy.tenant_domain_suffix'); @endphp
<x-central-layout title="Add Barangay" breadcrumb="Add Barangay">

    <div class="px-6 py-8 sm:px-10 max-w-3xl">

        {{-- back link --}}
        <a href="{{ route('central.tenants.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 font-medium mb-6 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Barangays
        </a>

        <div class="c-card overflow-hidden">

            {{-- card header --}}
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

            <form method="POST" action="{{ route('central.tenants.store') }}" class="px-7 py-7 space-y-6">
                @csrf

                @error('provisioning')
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex gap-2">
                        <svg class="w-5 h-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="name">
                        Barangay Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                        </div>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                               placeholder="e.g. Barangay Carmen"
                               class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition
                                      {{ $errors->has('name') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                    </div>
                    @error('name')<p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                    <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">
                        Full official name. The portal hostname will be generated as
                        <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 rounded">slug.{{ $domainSuffix }}</code>
                        (add it to your hosts file or DNS). If that name is taken, <code class="text-xs bg-slate-100 px-1 rounded">slug-2.{{ $domainSuffix }}</code> is used, and so on.
                    </p>
                </div>

                {{-- Plan --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="plan_id">
                        Subscription Plan
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <select id="plan_id" name="plan_id"
                                class="w-full pl-10 pr-10 py-2.5 text-sm border rounded-xl transition appearance-none
                                       {{ $errors->has('plan_id') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                            <option value="">— No plan (Free) —</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} — {{ $plan->monthly_reservation_limit }} reservations/mo
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </div>
                    </div>
                    @error('plan_id')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1.5 text-xs text-slate-400">Assign a subscription plan to control feature access and reservation limits.</p>
                </div>

                {{-- Officer accounts (tenant portal login) --}}
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/50 p-5 space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Portal login accounts</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">These users are created in the new tenant database. They sign in at the barangay hostname (e.g. <code class="text-xs bg-white dark:bg-slate-700 px-1 rounded border border-slate-200 dark:border-slate-600">slug.{{ $domainSuffix }}</code>) using the tenant login page — not the central admin.</p>
                    </div>

                    {{-- Secretary --}}
                    <div class="space-y-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Barangay Secretary</p>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="secretary_email">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input id="secretary_email" name="secretary_email" type="email" value="{{ old('secretary_email') }}" required autocomplete="email"
                                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                                          {{ $errors->has('secretary_email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                            @error('secretary_email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="secretary_password">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input id="secretary_password" name="secretary_password" type="password" required autocomplete="new-password"
                                       class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                                              {{ $errors->has('secretary_password') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                                @error('secretary_password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="secretary_password_confirmation">
                                    Confirm password <span class="text-red-500">*</span>
                                </label>
                                <input id="secretary_password_confirmation" name="secretary_password_confirmation" type="password" required autocomplete="new-password"
                                       class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            </div>
                        </div>
                    </div>

                    {{-- Captain --}}
                    <div class="space-y-4 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Punong Barangay (Captain)</p>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="captain_email">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input id="captain_email" name="captain_email" type="email" value="{{ old('captain_email') }}" required autocomplete="email"
                                   class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                                          {{ $errors->has('captain_email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                            @error('captain_email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="captain_password">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input id="captain_password" name="captain_password" type="password" required autocomplete="new-password"
                                       class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                                              {{ $errors->has('captain_password') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                                @error('captain_password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="captain_password_confirmation">
                                    Confirm password <span class="text-red-500">*</span>
                                </label>
                                <input id="captain_password_confirmation" name="captain_password_confirmation" type="password" required autocomplete="new-password"
                                       class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-600/25 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Register Barangay &amp; Provision Database
                    </button>
                    <a href="{{ route('central.tenants.index') }}"
                       class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-central-layout>
