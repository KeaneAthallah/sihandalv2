<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-danger border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
