<x-tenant-layout title="New reservation" breadcrumb="New reservation">

    <div class="px-6 py-8 sm:px-10 max-w-xl">
        <form method="POST" action="{{ route('tenant.reservations.store') }}" class="t-card p-6 sm:p-8 space-y-5">
            @csrf

            @if ($errors->any())
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
                <label class="t-label" for="facility_id">Facility or equipment</label>
                <select id="facility_id" name="facility_id" class="t-input" required>
                    <option value="">Select a listing…</option>
                    @foreach($facilities as $f)
                        <option value="{{ $f->id }}" @selected((string) old('facility_id', $preselectFacilityId) === (string) $f->id)>{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="t-label" for="starts_at">Start</label>
                <input id="starts_at" name="starts_at" type="datetime-local" class="t-input" value="{{ old('starts_at') }}" required />
            </div>
            <div>
                <label class="t-label" for="ends_at">End</label>
                <input id="ends_at" name="ends_at" type="datetime-local" class="t-input" value="{{ old('ends_at') }}" required />
            </div>
            <div>
                <label class="t-label" for="purpose">Purpose</label>
                <textarea id="purpose" name="purpose" rows="3" class="t-textarea" placeholder="Briefly describe the purpose of this reservation…">{{ old('purpose') }}</textarea>
            </div>
            @if($supportsIntegratedPayments)
                <div>
                    <label class="t-label" for="payment_option">Preferred payment method</label>
                    <select id="payment_option" name="payment_option" class="t-input">
                        <option value="">Select payment method (optional)</option>
                        @foreach(['cash', 'gcash', 'paymaya', 'bank_transfer', 'stripe', 'paypal'] as $option)
                            <option value="{{ $option }}" @selected(old('payment_option') === $option)>
                                {{ str($option)->replace('_', ' ')->title() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Premium feature: this helps the barangay prepare integrated payment handling.
                    </p>
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

</x-tenant-layout>
