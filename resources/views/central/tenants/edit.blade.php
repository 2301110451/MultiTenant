<x-central-layout title="Edit Barangay" breadcrumb="Edit Barangay">

    <div class="px-6 py-8 sm:px-10 max-w-2xl space-y-6" x-data="{ showDeleteModal: false }">

        {{-- back link --}}
        <a href="{{ route('central.tenants.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Back to Barangays
        </a>

        {{-- main card --}}
        <div class="bg-white border border-slate-200 rounded-2xl card-shadow overflow-hidden">

            {{-- card header --}}
            <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-7 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center font-bold text-white text-sm">
                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">{{ $tenant->name }}</h2>
                        <p class="text-slate-400 text-sm font-mono">{{ $tenant->domains->first()?->domain ?? '—' }}</p>
                    </div>
                    @php
                        $tenantStatus = $tenant->status ?? 'active';
                        $statusChipClass = match ($tenantStatus) {
                            'active' => 'bg-emerald-400/20 text-emerald-300',
                            'unsubscribed' => 'bg-slate-400/20 text-slate-200',
                            default => 'bg-red-400/20 text-red-300',
                        };
                    @endphp
                    <span class="ml-auto px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusChipClass }}">
                        {{ ucfirst($tenant->status ?? 'active') }}
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('central.tenants.update', $tenant) }}" class="px-7 py-7 space-y-6">
                @csrf @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="name">
                        Barangay Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                        </div>
                        <input id="name" name="name" type="text" value="{{ old('name', $tenant->name) }}" required
                               class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-xl transition
                                      {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                    </div>
                    @error('name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Domain --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="domain">
                        Tenant Domain <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5a17.92 17.92 0 01-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>
                            </svg>
                        </div>
                        <input id="domain" name="domain" type="text"
                               value="{{ old('domain', $tenant->domains->first()?->domain) }}" required
                               class="w-full pl-10 pr-4 py-2.5 text-sm font-mono border rounded-xl transition
                                      {{ $errors->has('domain') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100' }}">
                    </div>
                    @error('domain')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Plan --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="plan_id">
                        Subscription Plan
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg style="width:18px;height:18px" class="text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <select id="plan_id" name="plan_id"
                                class="w-full pl-10 pr-10 py-2.5 text-sm border rounded-xl appearance-none transition border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                            <option value="">— No plan —</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}"
                                    {{ old('plan_id', $tenant->plan_id) == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} — {{ $plan->monthly_reservation_limit }} reservations/mo
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </div>
                    </div>
                    @error('plan_id')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5" for="status">
                        Account Status <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-4">
                        @foreach(['active' => ['Active', 'text-emerald-700 border-emerald-400 bg-emerald-50'], 'suspended' => ['Suspended', 'text-red-700 border-red-400 bg-red-50'], 'unsubscribed' => ['Unsubscribed', 'text-slate-700 border-slate-400 bg-slate-100']] as $val => [$label, $cls])
                        <label class="flex items-center gap-2.5 cursor-pointer group">
                            <input type="radio" name="status" value="{{ $val }}"
                                   {{ old('status', $tenant->status ?? 'active') === $val ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            <span class="text-sm font-semibold {{ $cls }} px-2.5 py-0.5 rounded-full border text-xs">
                                {{ $label }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('status')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                        </svg>
                        Save Changes
                    </button>
                    <a href="{{ route('central.tenants.index') }}"
                       class="px-5 py-3 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- danger zone --}}
        <div class="bg-white border-2 border-red-200 rounded-2xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-red-700">Danger Zone</h3>
                    <p class="text-xs text-red-500 mt-1">
                        Permanently delete <strong>{{ $tenant->name }}</strong> and all associated data. This action cannot be undone.
                    </p>
                </div>
                <button
                    type="button"
                    @click="showDeleteModal = true"
                    class="shrink-0 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-sm shadow-red-600/30"
                >
                    Delete Barangay
                </button>
            </div>
        </div>

        <div
            x-show="showDeleteModal"
            x-transition.opacity
            class="fixed inset-0 z-[70] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4"
            style="display:none"
            @keydown.escape.window="showDeleteModal = false"
            @click.self="showDeleteModal = false"
        >
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-red-200 dark:border-red-700 shadow-xl p-6">
                <h3 class="text-lg font-bold text-red-700 dark:text-red-400">Delete {{ $tenant->name }}?</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    This action is permanent and cannot be undone.
                </p>
                <form method="POST" action="{{ route('central.tenants.destroy', $tenant) }}" class="mt-5 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700">
                        Delete Now
                    </button>
                </form>
            </div>
        </div>
    </div>

</x-central-layout>
