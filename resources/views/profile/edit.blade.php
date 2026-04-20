<x-central-layout title="Profile" breadcrumb="Profile">
    <div class="px-6 py-8 sm:px-10 space-y-6">
        <div class="slide-up">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Profile</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Manage your account information and security settings.
            </p>
        </div>

        <div class="c-card p-6 sm:p-8 slide-up slide-up-delay-1">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="c-card p-6 sm:p-8 slide-up slide-up-delay-2">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="c-card p-6 sm:p-8 slide-up slide-up-delay-3">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-central-layout>
