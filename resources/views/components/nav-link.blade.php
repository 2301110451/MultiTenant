@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-primary-500 px-1 pt-1 text-sm font-medium leading-5 text-neutral-900 transition duration-200 ease-in-out focus:outline-none'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-neutral-500 transition duration-200 ease-in-out hover:border-neutral-300 hover:text-neutral-700 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
