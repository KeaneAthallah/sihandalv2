@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-primary bg-primary/5 focus:outline-none focus:text-primary-dark focus:bg-primary/10 focus:border-primary-dark transition'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-content-secondary hover:text-content hover:bg-surface-alt hover:border-border-strong focus:outline-none focus:text-content focus:bg-surface-alt focus:border-border-strong transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
