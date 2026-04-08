<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-soft transition-all duration-200 ease-in-out hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/40 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
