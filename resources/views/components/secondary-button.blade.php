<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-lg border border-border bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-700 shadow-sm transition-all duration-200 ease-in-out hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-primary-500/25 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
