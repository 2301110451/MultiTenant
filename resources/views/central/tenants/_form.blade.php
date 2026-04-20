@php
    $domainSuffix = config('tenancy.tenant_domain_suffix');
    $isModal = ! empty($modal);
@endphp

<form method="POST" action="{{ route('central.tenants.store') }}" class="space-y-6">
    @csrf

    @error('provisioning')
        <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-200 flex gap-2">
            <svg class="w-5 h-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_name' : 'name' }}">
            Barangay Name <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
            </div>
            <input id="{{ $isModal ? 'modal_name' : 'name' }}" name="name" type="text" value="{{ old('name') }}" required
                   placeholder="e.g. Barangay Carmen"
                   class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition
                          {{ $errors->has('name') ? 'border-red-400 bg-red-50 dark:bg-red-900/20 focus:ring-red-300' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
        </div>
        @error('name')<p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">{{ $message }}</p>@enderror
        <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">
            Full official name. The portal hostname will be generated as
            <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 rounded">slug.{{ $domainSuffix }}</code>
            (add it to your hosts file or DNS). If that name is taken, <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 rounded">slug-2.{{ $domainSuffix }}</code> is used, and so on.
        </p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_plan_id' : 'plan_id' }}">
            Subscription Plan
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <select id="{{ $isModal ? 'modal_plan_id' : 'plan_id' }}" name="plan_id"
                    class="w-full pl-10 pr-10 py-2.5 text-sm border rounded-xl transition appearance-none
                           {{ $errors->has('plan_id') ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                <option value="">— No plan (Free) —</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} — {{ $plan->monthly_reservation_limit === null ? 'Unlimited' : number_format($plan->monthly_reservation_limit) }} reservations/mo
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

    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/50 p-5 space-y-5">
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Tenant login accounts</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">These users are created in the new tenant database. They sign in at the barangay hostname (e.g. <code class="text-xs bg-white dark:bg-slate-700 px-1 rounded border border-slate-200 dark:border-slate-600">slug.{{ $domainSuffix }}</code>) using the tenant login page — not the central admin.</p>
        </div>

        <div class="space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Tenant Admin (required)</p>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_tenant_admin_email' : 'tenant_admin_email' }}">
                    Email <span class="text-red-500">*</span>
                </label>
                <input id="{{ $isModal ? 'modal_tenant_admin_email' : 'tenant_admin_email' }}" name="tenant_admin_email" type="email" value="{{ old('tenant_admin_email') }}" required autocomplete="email"
                       class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                              {{ $errors->has('tenant_admin_email') ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                @error('tenant_admin_email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_tenant_admin_password' : 'tenant_admin_password' }}">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input id="{{ $isModal ? 'modal_tenant_admin_password' : 'tenant_admin_password' }}" name="tenant_admin_password" type="password" required autocomplete="new-password"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                                  {{ $errors->has('tenant_admin_password') ? 'border-red-400 bg-red-50' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                    @error('tenant_admin_password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_tenant_admin_password_confirmation' : 'tenant_admin_password_confirmation' }}">
                        Confirm password <span class="text-red-500">*</span>
                    </label>
                    <input id="{{ $isModal ? 'modal_tenant_admin_password_confirmation' : 'tenant_admin_password_confirmation' }}" name="tenant_admin_password_confirmation" type="password" required autocomplete="new-password"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>
        </div>

        <div class="space-y-4 pt-2 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Staff (optional)</p>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_staff_email' : 'staff_email' }}">
                    Email
                </label>
                <input id="{{ $isModal ? 'modal_staff_email' : 'staff_email' }}" name="staff_email" type="email" value="{{ old('staff_email') }}" autocomplete="email"
                       class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                              {{ $errors->has('staff_email') ? 'border-red-400 bg-red-50' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                @error('staff_email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_staff_password' : 'staff_password' }}">
                        Password
                    </label>
                    <input id="{{ $isModal ? 'modal_staff_password' : 'staff_password' }}" name="staff_password" type="password" autocomplete="new-password"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl transition
                                  {{ $errors->has('staff_password') ? 'border-red-400 bg-red-50' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                    @error('staff_password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="{{ $isModal ? 'modal_staff_password_confirmation' : 'staff_password_confirmation' }}">
                        Confirm password
                    </label>
                    <input id="{{ $isModal ? 'modal_staff_password_confirmation' : 'staff_password_confirmation' }}" name="staff_password_confirmation" type="password" autocomplete="new-password"
                           class="w-full px-4 py-2.5 text-sm border rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
        <button type="submit"
                class="flex-1 flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-600/25 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Register Barangay &amp; Provision Database
        </button>
        @if($isModal)
            <button type="button" @click="showCreateModal = false" class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition sm:shrink-0">
                Cancel
            </button>
        @else
            <a href="{{ route('central.tenants.index') }}"
               class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition text-center sm:shrink-0">
                Cancel
            </a>
        @endif
    </div>
</form>
