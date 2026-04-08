<x-central-layout title="Subscription Plans" breadcrumb="Plans">

    <div class="px-6 py-8 sm:px-10 space-y-6">

        {{-- header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Subscription Plans</h1>
                <p class="mt-0.5 text-sm text-slate-500">Define the tiers that control feature access for each barangay tenant.</p>
            </div>
            <a href="{{ route('central.plans.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-indigo-600/30 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Create Plan
            </a>
        </div>

        {{-- flash --}}
        @if(session('status'))
        <div class="flex items-center gap-2.5 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @php $msg = ['plan-created'=>'Plan created.','plan-updated'=>'Plan updated.','plan-deleted'=>'Plan deleted.']; @endphp
            {{ $msg[session('status')] ?? session('status') }}
        </div>
        @endif

        @if($plans->isEmpty())
            <div class="py-20 text-center c-card">
                <svg class="w-14 h-14 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-slate-400 font-semibold">No subscription plans yet.</p>
                <a href="{{ route('central.plans.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">Create the first plan</a>
            </div>
        @else
        {{-- pricing card grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            @php
            $tierAccents = [
                0 => ['border'=>'border-slate-200','badge'=>'bg-slate-100 text-slate-700','btn'=>'bg-slate-700 hover:bg-slate-800','icon'=>'bg-slate-100 text-slate-600'],
                1 => ['border'=>'border-blue-200','badge'=>'bg-blue-100 text-blue-700','btn'=>'bg-blue-600 hover:bg-blue-700','icon'=>'bg-blue-100 text-blue-600'],
                2 => ['border'=>'border-amber-300','badge'=>'bg-amber-100 text-amber-700','btn'=>'bg-amber-500 hover:bg-amber-600','icon'=>'bg-amber-100 text-amber-600'],
            ];
            $knownFeatures = [
                'reports'  => ['Reports & Analytics',  'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
                'qr'       => ['QR Check-in / Check-out', 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z'],
                'payments' => ['Payment Integration',   'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.348 4.5a4.502 4.502 0 006.304-6.304L12 10.5'],
            ];
            @endphp

            @foreach($plans as $i => $plan)
            @php $a = $tierAccents[$i % 3]; @endphp
            <div class="bg-white dark:bg-slate-900 border-2 {{ $a['border'] }} rounded-2xl card-shadow flex flex-col overflow-hidden hover:shadow-lg transition-shadow">

                {{-- plan header --}}
                <div class="px-6 pt-6 pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl {{ $a['icon'] }} flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $a['badge'] }}">
                            {{ $plan->slug }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $plan->name }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        @if($plan->monthly_reservation_limit)
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($plan->monthly_reservation_limit) }}</span> reservations / month
                        @else
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Unlimited</span> reservations
                        @endif
                    </p>
                </div>

                {{-- feature list --}}
                <div class="px-6 py-5 flex-1 space-y-2.5">
                    @php $features = $plan->features ?? []; @endphp
                    @foreach($knownFeatures as $key => [$label, $icon])
                        @php $active = in_array($key, $features) || isset($features[$key]); @endphp
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center {{ $active ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-slate-100 dark:bg-slate-800' }}">
                                <svg class="w-3 h-3 {{ $active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}"
                                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    @if($active)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    @endif
                                </svg>
                            </div>
                            <span class="text-sm {{ $active ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500 line-through' }}">{{ $label }}</span>
                        </div>
                    @endforeach
                    {{-- custom features --}}
                    @foreach($features as $key => $val)
                        @if(!isset($knownFeatures[$key]) && is_string($key))
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </div>
                            <span class="text-sm text-slate-700">{{ ucwords(str_replace('_', ' ', $key)) }}: {{ $val }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>

                {{-- actions --}}
                <div class="px-6 pb-6 flex items-center gap-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                    <a href="{{ route('central.plans.edit', $plan) }}"
                       class="flex-1 flex items-center justify-center gap-2 py-2.5 {{ $a['btn'] }} text-white text-sm font-semibold rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('central.plans.destroy', $plan) }}"
                          onsubmit="return confirm('Delete plan {{ addslashes($plan->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</x-central-layout>
