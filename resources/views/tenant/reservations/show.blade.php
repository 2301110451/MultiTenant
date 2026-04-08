@php $tb = \App\Support\TenantAppearance::theme()['button']; @endphp
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
                @if($reservation->qr_token)
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <dt class="font-semibold text-slate-600 dark:text-slate-400 mb-1">QR check-in token</dt>
                        <dd><code class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-1 rounded-lg font-mono">{{ $reservation->qr_token }}</code></dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Officer: update status --}}
        @if(auth('tenant')->user()->isSecretary() || auth('tenant')->user()->isCaptain())
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
                <button type="submit" class="w-full py-3 rounded-xl text-white text-sm font-semibold shadow-sm transition {{ $tb }}">
                    Update reservation
                </button>
            </form>
        @endif

        {{-- Delete (officers only) --}}
        @if(auth('tenant')->user()->isSecretary() || auth('tenant')->user()->isCaptain())
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
