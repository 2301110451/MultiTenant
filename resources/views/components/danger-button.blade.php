<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-soft transition-all duration-200 ease-in-out hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40']) }}>
    {{ $slot }}
</button>
