<x-central-guest-layout>
    <div class="space-y-5">
        <div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Tenant Portal Login</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Select your barangay, then continue to that portal's login page.
            </p>
        </div>

        @if ($portals->isEmpty())
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                No active barangay portals are available right now.
            </div>
        @else
            <form method="POST" action="{{ route('tenant.login.selector.redirect') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="domain" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Barangay portal
                    </label>

                    <select
                        id="domain"
                        name="domain"
                        required
                        class="w-full rounded-xl border px-3 py-2.5 text-sm transition dark:bg-slate-800 dark:text-slate-100 {{ $errors->has('domain') ? 'border-red-400 bg-red-50 dark:bg-red-900/30' : 'border-slate-300 dark:border-slate-600 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900' }}"
                    >
                        <option value="">Choose a barangay...</option>
                        @foreach ($portals as $portal)
                            <option value="{{ $portal->domain }}" @selected(old('domain') === $portal->domain)>
                                {{ $portal->tenant_name }} ({{ $portal->domain }})
                            </option>
                        @endforeach
                    </select>

                    @error('domain')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:-translate-y-0.5 hover:from-indigo-700 hover:to-violet-700 focus:outline-none focus:ring-4 focus:ring-indigo-300"
                >
                    Continue to tenant login
                </button>
            </form>
        @endif

        <p class="pt-1 text-center text-sm text-slate-500 dark:text-slate-400">
            Central admin?
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">
                Sign in here
            </a>
        </p>
    </div>
</x-central-guest-layout>
