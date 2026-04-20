<x-tenant-layout title="Reservation #{{ $reservation->id }}" breadcrumb="View Reservation">

    <div class="px-6 py-8 sm:px-10 max-w-xl space-y-6">

        {{-- Details card --}}
        <div class="t-card p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Reservation Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-slate-600 dark:text-slate-400">Facility</dt>
                    <dd class="text-slate-900 dark:text-slate-100 text-right">{{ $reservation->facility->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-slate-600 dark:text-slate-400">Requested by</dt>
                    <dd class="text-slate-900 dark:text-slate-100 text-right">{{ $reservation->user->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-slate-600 dark:text-slate-400">Start</dt>
                    <dd class="text-slate-900 dark:text-slate-100 text-right">{{ $reservation->starts_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-slate-600 dark:text-slate-400">End</dt>
                    <dd class="text-slate-900 dark:text-slate-100 text-right">{{ $reservation->ends_at->format('M d, Y H:i') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-slate-600 dark:text-slate-400">Status</dt>
                    <dd>
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
                    </dd>
                </div>
                @if($reservation->payment_option)
                    <div class="flex justify-between gap-4">
                        <dt class="font-semibold text-slate-600 dark:text-slate-400">Payment option</dt>
                        <dd class="text-slate-900 dark:text-slate-100 text-right">
                            {{ str($reservation->payment_option)->replace('_', ' ')->title() }}
                        </dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <dt class="font-semibold text-slate-600 dark:text-slate-400">Returned at</dt>
                    <dd class="text-slate-900 dark:text-slate-100 text-right">
                        {{ $reservation->checked_out_at?->format('M d, Y H:i') ?? 'Not yet returned' }}
                    </dd>
                </div>
                @if($reservation->qr_token)
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <dt class="font-semibold text-slate-600 dark:text-slate-400 mb-1">QR check-in token</dt>
                        <dd><code class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-1 rounded-lg font-mono">{{ $reservation->qr_token }}</code></dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Resident: mark returned --}}
        @if(auth('tenant')->user()->isResident() && in_array($reservation->status->value, ['approved', 'completed']))
            <form method="POST" action="{{ route('tenant.reservations.mark-returned', $reservation) }}" class="t-card p-6 space-y-3">
                @csrf
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Return Confirmation</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Use this once you have returned the rented facility/equipment.
                </p>
                <button type="submit" class="t-btn-primary w-full justify-center py-3 shadow-sm">
                    Mark as Returned
                </button>
            </form>
        @endif

        {{-- Officer: update status --}}
        @if(auth('tenant')->user()->canManageTenant())
            <form method="POST" action="{{ route('tenant.reservations.update', $reservation) }}" class="t-card p-6 space-y-4">
                @csrf
                @method('PUT')
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Update Status</h2>
                <div>
                    <label class="t-label" for="status">New status</label>
                    <select id="status" name="status" class="t-input">
                        @foreach(['pending','approved','rejected','completed'] as $s)
                            <option value="{{ $s }}" @selected($reservation->status->value === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="t-btn-primary w-full justify-center py-3 shadow-sm">
                    Update reservation
                </button>
            </form>

            <form method="POST" action="{{ route('tenant.reservations.damage', $reservation) }}" class="t-card p-6 space-y-4">
                @csrf
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Mark as Damaged / Set Amount</h2>
                <div>
                    <label class="t-label" for="amount">Amount to pay (PHP)</label>
                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" class="t-input" required>
                </div>
                <div>
                    <label class="t-label" for="description">Damage note (optional)</label>
                    <textarea id="description" name="description" rows="3" class="t-input" placeholder="Describe the damage or penalty reason"></textarea>
                </div>
                <button type="submit" class="w-full py-3 rounded-xl text-white text-sm font-semibold shadow-sm transition bg-red-600 hover:bg-red-700">
                    Save damage charge and notify renter
                </button>
            </form>
        @endif

        {{-- Damage and payment tracking --}}
        <div class="t-card p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Damage / Payment Status</h2>
            @php
                $damagePayments = $reservation->payments->where('method', 'damage');
            @endphp
            @if($damagePayments->isEmpty())
                <p class="text-sm text-slate-500 dark:text-slate-400">No damage charges yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($damagePayments as $payment)
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    PHP {{ number_format((float) $payment->amount, 2) }}
                                </p>
                                <span class="inline-flex items-center text-xs font-semibold border rounded-full px-2.5 py-0.5 capitalize {{ $payment->status === 'paid' ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700' : 'text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700' }}">
                                    {{ $payment->status }}
                                </span>
                            </div>
                            @if($payment->external_ref)
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $payment->external_ref }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $payment->paid_at ? 'Paid at '.$payment->paid_at->format('M d, Y H:i') : 'Pending payment' }}
                            </p>

                            @if((auth('tenant')->user()->isResident() || auth('tenant')->user()->canManageTenant()) && $payment->status !== 'paid')
                                <form method="POST" action="{{ route('tenant.reservations.payments.paid', [$reservation, $payment]) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700">
                                        {{ auth('tenant')->user()->canManageTenant() ? 'Confirm as Paid' : 'Mark as Paid' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Delete (officers only) --}}
        @if(auth('tenant')->user()->canManageTenant())
            <form method="POST" action="{{ route('tenant.reservations.destroy', $reservation) }}"
                  onsubmit="return confirm('Cancel this reservation? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                    Cancel reservation
                </button>
            </form>
        @endif
    </div>

</x-tenant-layout>
