<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-danger border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-danger/30 focus:ring-offset-2 disabled:opacity-50 transition']) }}>
    {{ $slot }}
</button>
