<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New Feature Preview
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-3">
                <h1 class="text-2xl font-bold text-gray-900">{{ $featureName }}</h1>
                <p class="text-sm text-gray-600">Feature version: {{ $featureVersion }}</p>
                <p class="text-sm text-gray-600">Status: {{ $status }}</p>
                <p class="text-sm text-gray-600">
                    This page is only for testing Central Release detection when a new feature is pushed to GitHub.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
