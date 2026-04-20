<form method="POST" x-bind:action="'{{ url('/central/tenants') }}/' + edit.id" class="space-y-6">
    @csrf
    @method('PUT')
    <input type="hidden" name="edit_tenant_id" x-bind:value="edit.id">
    @php
        $pagePreserve = old('page', request('page'));
    @endphp
    @if($pagePreserve !== null && $pagePreserve !== '')
        <input type="hidden" name="page" value="{{ $pagePreserve }}">
    @endif
    @if(! empty($redirectTo))
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
    @endif

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="edit_modal_name">
            Barangay Name <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
            </div>
            <input id="edit_modal_name" name="name" type="text" x-model="edit.name" required
                   class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition
                          {{ $errors->has('name') ? 'border-red-400 bg-red-50 dark:bg-red-900/20 focus:ring-red-300' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/40' }}">
        </div>
        @error('name')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="edit_modal_domain">
            Tenant Domain <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5a17.92 17.92 0 01-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                </svg>
            </div>
            <input id="edit_modal_domain" name="domain" type="text" x-model="edit.domain" required
                   class="w-full pl-10 pr-4 py-2.5 text-sm font-mono border rounded-xl transition
                          {{ $errors->has('domain') ? 'border-red-400 bg-red-50 dark:bg-red-900/20 focus:ring-red-300' : 'border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/40' }}">
        </div>
        @error('domain')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5" for="edit_modal_plan_id">
            Subscription Plan
        </label>
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <select id="edit_modal_plan_id" name="plan_id" x-model="edit.plan_id"
                    class="w-full pl-10 pr-10 py-2.5 text-sm border rounded-xl appearance-none transition border-slate-300 dark:border-slate-600 dark:bg-slate-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/40 {{ $errors->has('plan_id') ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : '' }}">
                <option value="">— No plan —</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }} — {{ $plan->monthly_reservation_limit }} reservations/mo</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </div>
        @error('plan_id')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Account Status <span class="text-red-500">*</span>
        </label>
        <div class="flex flex-wrap gap-4">
            @foreach(['active' => ['Active', 'text-emerald-700 border-emerald-400 bg-emerald-50 dark:text-emerald-300 dark:border-emerald-500/50 dark:bg-emerald-900/20'], 'suspended' => ['Suspended', 'text-red-700 border-red-400 bg-red-50 dark:text-red-300 dark:border-red-500/50 dark:bg-red-900/20'], 'unsubscribed' => ['Unsubscribed', 'text-slate-700 border-slate-400 bg-slate-100 dark:text-slate-200 dark:border-slate-500 dark:bg-slate-800']] as $val => [$label, $cls])
            <label class="flex items-center gap-2.5 cursor-pointer group">
                <input type="radio" name="status" value="{{ $val }}" x-model="edit.status"
                       class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 focus:ring-indigo-500">
                <span class="text-sm font-semibold {{ $cls }} px-2.5 py-0.5 rounded-full border text-xs">
                    {{ $label }}
                </span>
            </label>
            @endforeach
        </div>
        @error('status')<p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center gap-3 pt-2 border-t border-slate-100 dark:border-slate-700">
        <button type="submit"
                class="flex-1 flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
            </svg>
            Save Changes
        </button>
        <button type="button" @click="closeEditModal()"
                class="px-5 py-3 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition">
            Cancel
        </button>
    </div>
</form>
