@php
    $isCentral = \App\Support\Tenancy::isCentralHost(request()->getHost());
    $centralUser = auth('web')->user();
    $tenantUser = auth('tenant')->user();
    $authUser = $centralUser ?? $tenantUser;
@endphp

<nav x-data="{ open: false }" class="border-b border-border bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="text-lg font-semibold text-neutral-900">
                        {{ config('app.name') }}
                    </a>
                </div>

                @auth('web')
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('central.tenants.index')" :active="request()->routeIs('central.tenants.*')">
                            {{ __('Tenants') }}
                        </x-nav-link>
                        <x-nav-link :href="route('central.plans.index')" :active="request()->routeIs('central.plans.*')">
                            {{ __('Plans') }}
                        </x-nav-link>
                    </div>
                @endauth

                @auth('tenant')
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tenant.facilities.index')" :active="request()->routeIs('tenant.facilities.*')">
                            {{ __('Facilities') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tenant.reservations.index')" :active="request()->routeIs('tenant.reservations.*')">
                            {{ __('Reservations') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tenant.calendar')" :active="request()->routeIs('tenant.calendar')">
                            {{ __('Calendar') }}
                        </x-nav-link>
                        <x-nav-link :href="route('tenant.reports.index')" :active="request()->routeIs('tenant.reports.*')">
                            {{ __('Reports') }}
                        </x-nav-link>
                    </div>
                @endauth
            </div>

            @if($authUser)
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex items-center rounded-lg border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-neutral-500 transition duration-200 ease-in-out hover:bg-neutral-50 hover:text-neutral-700 focus:outline-none">
                            <div>{{ $authUser->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if($isCentral && Route::has('profile.edit'))
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @else
            <div class="hidden sm:flex sm:items-center gap-4">
                <a href="{{ route('login') }}" class="text-sm text-neutral-600 hover:text-neutral-900">{{ __('Log in') }}</a>
                @if(! $isCentral)
                    <a href="{{ route('register') }}" class="text-sm font-medium text-primary-600 hover:text-primary-800">{{ __('Register') }}</a>
                @endif
            </div>
            @endif

            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="open = ! open" class="inline-flex items-center justify-center rounded-lg p-2 text-neutral-400 transition duration-150 ease-in-out hover:bg-neutral-100 hover:text-neutral-500 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        @auth('web')
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('central.tenants.index')">{{ __('Tenants') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('central.plans.index')">{{ __('Plans') }}</x-responsive-nav-link>
            </div>
        @endauth
        @auth('tenant')
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tenant.facilities.index')">{{ __('Facilities') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tenant.reservations.index')">{{ __('Reservations') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tenant.calendar')">{{ __('Calendar') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tenant.reports.index')">{{ __('Reports') }}</x-responsive-nav-link>
            </div>
        @endauth

        @if($authUser)
        <div class="border-t border-border pt-4 pb-1">
            <div class="px-4">
                <div class="text-base font-medium text-neutral-900">{{ $authUser->name }}</div>
                <div class="text-sm font-medium text-neutral-500">{{ $authUser->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                @if($isCentral && Route::has('profile.edit'))
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endif
    </div>
</nav>
