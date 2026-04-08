@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-primary-500 bg-primary-50 py-2 ps-3 pe-4 text-start text-base font-medium text-primary-700 transition duration-200 ease-in-out focus:outline-none'
            : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-neutral-600 transition duration-200 ease-in-out hover:border-neutral-300 hover:bg-neutral-50 hover:text-neutral-800 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
