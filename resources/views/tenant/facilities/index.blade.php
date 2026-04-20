@php
    use App\Enums\FacilityKind;
    $pageTitle = match ($kindFilter) {
        'equipment' => 'Equipment',
        'facility' => 'Facilities',
        default => $canManage ? 'Facilities & equipment' : 'Browse',
    };
    $pageSubtitle = match ($kindFilter) {
        'equipment' => 'Portable and lendable items you can reserve by time slot.',
        'facility' => 'Halls, courts, and bookable spaces.',
        default => $canManage ? 'Manage spaces and rentable items. Use the tabs to filter.' : 'Available halls, courts, and rentable items.',
    };
@endphp
<x-tenant-layout :title="$pageTitle" :breadcrumb="$pageTitle">
    <div class="px-6 py-8 sm:px-10">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $pageTitle }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl">{{ $pageSubtitle }}</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 shrink-0">
                @if($canManage)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('tenant.facilities.index') }}"
                           class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold border transition-all duration-200 ease-out
                                  {{ $kindFilter === null ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm hover:brightness-110 hover:shadow-md active:scale-[0.98]' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-500 hover:-translate-y-0.5 hover:shadow-sm active:translate-y-0' }}">
                            All
                        </a>
                        <a href="{{ route('tenant.facilities.index', ['kind' => 'facility']) }}"
                           class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold border transition-all duration-200 ease-out
                                  {{ $kindFilter === 'facility' ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm hover:brightness-110 hover:shadow-md active:scale-[0.98]' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-500 hover:-translate-y-0.5 hover:shadow-sm active:translate-y-0' }}">
                            Facilities
                        </a>
                        <a href="{{ route('tenant.facilities.index', ['kind' => 'equipment']) }}"
                           class="inline-flex items-center rounded-xl px-3 py-2 text-xs font-semibold border transition-all duration-200 ease-out
                                  {{ $kindFilter === 'equipment' ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm hover:brightness-110 hover:shadow-md active:scale-[0.98]' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-500 hover:-translate-y-0.5 hover:shadow-sm active:translate-y-0' }}">
                            Equipment
                        </a>
                    </div>
                    <a href="{{ route('tenant.facilities.create') }}"
                       class="inline-flex items-center justify-center gap-2 t-btn-primary px-5 py-2.5 shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Add listing
                    </a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-5 text-sm text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl px-4 py-3">
                @if (session('status') === 'facility-created')
                    Listing created successfully.
                @elseif (session('status') === 'facility-updated')
                    Listing updated successfully.
                @elseif (session('status') === 'facility-deleted')
                    Listing removed.
                @else
                    {{ session('status') }}
                @endif
            </div>
        @endif

        @if($facilities->isEmpty())
            <div class="t-card p-12 text-center text-sm text-slate-500 dark:text-slate-400">
                @if($canManage)
                    Nothing here yet.
                    <a href="{{ route('tenant.facilities.create') }}" class="t-link hover:underline">Add a facility or equipment listing</a>.
                @else
                    No active listings in this category right now.
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($facilities as $facility)
                    <article class="t-card overflow-hidden flex flex-col group shadow-sm transition-all duration-300 ease-out hover:shadow-xl hover:-translate-y-1.5 hover:border-slate-300/90 dark:hover:border-slate-500/80">
                        <div class="relative aspect-[4/3] bg-slate-100 dark:bg-slate-800 overflow-hidden transition-[filter] duration-300 group-hover:brightness-[1.06] dark:group-hover:brightness-110">
                            @if($facility->image_path)
                                <img
                                    src="{{ route('tenant.facilities.image', $facility) }}"
                                    alt="{{ $facility->name }} photo"
                                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
                                    loading="lazy"
                                >
                            @else
                                <div class="absolute inset-0 flex items-center justify-center select-none" aria-hidden="true">
                                    <span class="text-7xl sm:text-8xl leading-none drop-shadow-sm transition-transform duration-300 ease-out group-hover:scale-110">{{ $facility->kind->emoji() }}</span>
                                </div>
                            @endif
                            <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wide rounded-full px-2.5 py-1 bg-white/95 dark:bg-slate-900/90 text-slate-800 dark:text-slate-100 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-600">
                                    {{ $facility->kind === FacilityKind::Equipment ? 'Equipment' : 'Facility' }}
                                </span>
                                @if($facility->is_active)
                                    <span class="text-[11px] font-semibold rounded-full px-2.5 py-1 bg-emerald-500 text-white shadow-sm">Active</span>
                                @else
                                    <span class="text-[11px] font-semibold rounded-full px-2.5 py-1 bg-slate-600 text-white shadow-sm">Inactive</span>
                                @endif
                            </div>
                            @if(! $canManage && $canReserve && $facility->is_active)
                                <a href="{{ route('tenant.reservations.create', ['facility_id' => $facility->id]) }}"
                                   class="absolute bottom-3 right-3 w-12 h-12 rounded-full bg-amber-400 text-slate-900 shadow-lg flex items-center justify-center transition-all duration-200 ease-out scale-100 hover:scale-110 hover:bg-amber-300 hover:shadow-xl hover:shadow-amber-500/30 group-hover:ring-2 group-hover:ring-amber-300/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 active:scale-95"
                                   title="Reserve this listing">
                                    <span class="sr-only">Reserve</span>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <div class="p-5 flex-1 flex flex-col transition-colors duration-300 group-hover:bg-slate-50/80 dark:group-hover:bg-slate-800/40">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-snug line-clamp-2 transition-colors duration-200 group-hover:text-accent">{{ $facility->name }}</h2>
                            @php
                                $inUseReservation = $blockingByFacilityId->get($facility->id);
                                $tz = config('app.timezone');
                            @endphp
                            @if (! $facility->is_active)
                                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-950 dark:border-amber-800 dark:bg-amber-950/50 dark:text-amber-100">
                                    <p class="font-semibold">Not available</p>
                                    <p class="mt-1 text-amber-900/90 dark:text-amber-200/95">This listing is turned off, so residents cannot book it. Turn it <strong class="font-medium">Active</strong> in Edit when it should be bookable again. The portal does not store a future “reopen” date.</p>
                                </div>
                            @elseif($inUseReservation)
                                <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2.5 text-xs text-sky-950 dark:border-sky-800 dark:bg-sky-950/50 dark:text-sky-100">
                                    <p class="font-semibold">In use right now</p>
                                    <p class="mt-1 text-sky-900/90 dark:text-sky-200/95">
                                        Available again after
                                        <strong class="font-semibold">{{ $inUseReservation->ends_at->timezone($tz)->format('M j, Y \a\t g:i A') }}</strong>
                                        <span class="text-sky-800/80 dark:text-sky-300/90">({{ $tz }})</span>.
                                        You can still open <strong class="font-medium">Reserve</strong> to choose another time.
                                    </p>
                                </div>
                            @endif
                            @if($facility->description)
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 line-clamp-2">{{ $facility->description }}</p>
                            @endif
                            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Capacity</dt>
                                    <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $facility->capacity }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Rate / hr</dt>
                                    <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ number_format((float) $facility->hourly_rate, 2) }}</dd>
                                </div>
                            </dl>
                            <div class="mt-5 flex flex-wrap items-center gap-2 pt-auto">
                                @if($canManage)
                                    <a
                                        href="{{ route('tenant.facilities.edit', $facility) }}"
                                        class="inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-xs font-semibold border border-slate-300 text-slate-700 hover:bg-slate-100 hover:border-slate-400 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700/60 transition-colors"
                                    >
                                        Edit listing
                                    </a>
                                @elseif($canReserve && $facility->is_active)
                                    <a
                                        href="{{ route('tenant.reservations.create', ['facility_id' => $facility->id]) }}"
                                        class="inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-xs font-semibold bg-amber-400 text-slate-900 hover:bg-amber-300 transition-colors shadow-sm"
                                    >
                                        Reserve
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8">{{ $facilities->links() }}</div>
        @endif
    </div>
</x-tenant-layout>
