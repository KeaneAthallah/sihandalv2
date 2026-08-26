<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-primary border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm hover:bg-primary-dark active:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/30 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
