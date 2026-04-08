@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'w-full rounded-lg border-border bg-white px-3.5 py-2.5 text-sm text-neutral-800 shadow-sm transition-all duration-200 ease-in-out placeholder:text-neutral-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25']) }}
>
