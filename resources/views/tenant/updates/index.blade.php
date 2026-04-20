<x-tenant-layout title="System Updates" breadcrumb="System Updates">
    <div class="px-6 py-8 sm:px-10 space-y-6" data-live-endpoint="{{ route('tenant.realtime.updates') }}" data-live-interval="15000">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">System Updates</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Announcements: <strong data-live-key="updateCount">{{ $updateCount }}</strong>
            <span class="ml-2">Latest: <strong data-live-key="latestPublishedAt">{{ optional($latestPublishedAt)?->format('M d, Y H:i') ?? 'N/A' }}</strong></span>
        </p>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        @if($canManageAnnouncements)
            <div class="t-card p-6">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Publish announcement to users</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    This publishes on dashboard and update feed. You can also send it to all active user emails.
                </p>

                <form method="POST" action="{{ route('tenant.announcements.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="announcement_title" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Title</label>
                        <input
                            id="announcement_title"
                            name="title"
                            type="text"
                            maxlength="255"
                            required
                            value="{{ old('title') }}"
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-900/40"
                        >
                        @error('title')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="announcement_message" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Message</label>
                        <textarea
                            id="announcement_message"
                            name="message"
                            rows="4"
                            maxlength="5000"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-900/40"
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" name="send_email" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('send_email', '1') === '1')>
                        Send this announcement to active users via email
                    </label>

                    <div>
                        <button type="submit" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-900/40">
                            Publish announcement
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="t-card p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Barangay announcements</h2>
            @forelse($tenantAnnouncements as $u)
                <div class="group rounded-xl border border-slate-200 dark:border-slate-700 bg-white/0 dark:bg-transparent p-4 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300/90 dark:hover:border-slate-500/80 hover:bg-slate-50/90 dark:hover:bg-slate-800/50">
                    <p class="font-semibold text-slate-900 dark:text-slate-100 transition-colors duration-200 group-hover:text-accent">{{ $u->title }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $u->published_at?->format('M d, Y H:i') }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-2">{{ $u->message }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No barangay announcements yet.</p>
            @endforelse
        </div>

        <div class="t-card p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Platform announcements</h2>
            @forelse($systemUpdates as $u)
                <div class="group rounded-xl border border-slate-200 dark:border-slate-700 bg-white/0 dark:bg-transparent p-4 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300/90 dark:hover:border-slate-500/80 hover:bg-slate-50/90 dark:hover:bg-slate-800/50">
                    <p class="font-semibold text-slate-900 dark:text-slate-100 transition-colors duration-200 group-hover:text-accent">{{ $u->title }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $u->published_at?->format('M d, Y H:i') }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-2">{{ $u->message }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No platform announcements yet.</p>
            @endforelse
        </div>
    </div>
</x-tenant-layout>
