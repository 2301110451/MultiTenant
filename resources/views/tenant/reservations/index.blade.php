<x-tenant-layout title="Reservations" breadcrumb="Reservations">
    @php
        $modal = $modal ?? '';
        $isCreateModalOpen = $canCreate && $modal === 'create-reservation';
        $openCreateModalUrl = route('tenant.reservations.index', ['modal' => 'create-reservation']);
        $closeModalUrl = route('tenant.reservations.index');
    @endphp

    <div class="px-6 py-8 sm:px-10" data-live-endpoint="{{ route('tenant.realtime.reservations') }}" data-live-interval="12000">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Reservations</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track and manage booking requests.</p>
            </div>
            @if($canCreate)
                <a href="{{ $openCreateModalUrl }}"
                   class="inline-flex items-center justify-center gap-2 t-btn-primary px-5 py-2.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    New reservation
                </a>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="t-card p-3"><p class="text-xs text-slate-500">Pending</p><p class="text-xl font-bold" data-live-key="pending">0</p></div>
            <div class="t-card p-3"><p class="text-xs text-slate-500">Approved</p><p class="text-xl font-bold" data-live-key="approved">0</p></div>
            <div class="t-card p-3"><p class="text-xs text-slate-500">Completed</p><p class="text-xl font-bold" data-live-key="completed">0</p></div>
            <div class="t-card p-3"><p class="text-xs text-slate-500">Rejected</p><p class="text-xl font-bold" data-live-key="rejected">0</p></div>
        </div>

        @if (session('status'))
            <div class="mb-5 text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="t-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Facility</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">When</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Requested by</th>
                            <th class="text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($reservations as $reservation)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-3.5 font-medium text-slate-900 dark:text-slate-100">{{ $reservation->facility->name }}</td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">
                                    {{ $reservation->starts_at->format('M d, H:i') }} &mdash; {{ $reservation->ends_at->format('H:i') }}
                                </td>
                                <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">{{ $reservation->user->name }}</td>
                                <td class="px-6 py-3.5">
                                    @php
                                        $statusColors = [
                                            'pending'   => 'text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700',
                                            'approved'  => 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700',
                                            'rejected'  => 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700',
                                            'completed' => 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                                        ];
                                        $sc = $statusColors[$reservation->status->value] ?? 'text-slate-600 bg-slate-100 border-slate-200 dark:text-slate-400 dark:bg-slate-800 dark:border-slate-700';
                                    @endphp
                                    <span class="inline-flex items-center text-xs font-semibold border rounded-full px-2.5 py-0.5 capitalize {{ $sc }}">
                                        {{ $reservation->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="{{ route('tenant.reservations.show', $reservation) }}"
                                       class="text-sm t-link">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400 dark:text-slate-500">
                                    No reservations yet. <a href="{{ $openCreateModalUrl }}" class="t-link hover:underline">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">{{ $reservations->links() }}</div>
        </div>
    </div>

    @if($canCreate)
        <x-modal name="create-reservation-modal" :show="$isCreateModalOpen" maxWidth="2xl">
            <div class="p-6 sm:p-8">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">New reservation</h2>
                    <a href="{{ $closeModalUrl }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Close</a>
                </div>
                <form method="POST" action="{{ route('tenant.reservations.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="_modal_context" value="create-reservation">

                    @if ($isCreateModalOpen && $errors->any())
                        <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @error('plan')
                        <div class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3">{{ $message }}</div>
                    @enderror

                    <div>
                        <label class="t-label" for="reservation_create_facility_id">Facility or equipment</label>
                        <select id="reservation_create_facility_id" name="facility_id" class="t-input" required>
                            <option value="">Select a listing…</option>
                            @foreach($facilities as $f)
                                <option value="{{ $f->id }}" @selected((string) old('facility_id', $preselectFacilityId) === (string) $f->id)>{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="t-label" for="reservation_create_starts_at">Start</label>
                        <input id="reservation_create_starts_at" name="starts_at" type="datetime-local" class="t-input" value="{{ old('starts_at') }}" required />
                    </div>
                    <div>
                        <label class="t-label" for="reservation_create_ends_at">End</label>
                        <input id="reservation_create_ends_at" name="ends_at" type="datetime-local" class="t-input" value="{{ old('ends_at') }}" required />
                    </div>
                    <div>
                        <label class="t-label" for="reservation_create_purpose">Purpose</label>
                        <textarea id="reservation_create_purpose" name="purpose" rows="3" class="t-textarea" placeholder="Briefly describe the purpose of this reservation…">{{ old('purpose') }}</textarea>
                    </div>
                    @if($supportsIntegratedPayments)
                        <div>
                            <label class="t-label" for="reservation_create_payment_option">Preferred payment method</label>
                            <select id="reservation_create_payment_option" name="payment_option" class="t-input">
                                <option value="">Select payment method (optional)</option>
                                @foreach(['cash', 'gcash', 'paymaya', 'bank_transfer', 'stripe', 'paypal'] as $option)
                                    <option value="{{ $option }}" @selected(old('payment_option') === $option)>
                                        {{ str($option)->replace('_', ' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="is_special_request" value="1"
                               @checked(old('is_special_request'))
                               class="t-checkbox-accent rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800" />
                        <span class="text-sm text-slate-700 dark:text-slate-300">Special request (requires tenant management approval)</span>
                    </label>
                    <button type="submit" class="t-btn-primary w-full justify-center py-3 shadow-sm">
                        Submit request
                    </button>
                </form>
            </div>
        </x-modal>
    @endif

</x-tenant-layout>
