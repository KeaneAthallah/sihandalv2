@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    <div class="card-header">
        <div class="min-w-0">
            <h3 class="card-title">{{ $title }}</h3>
            @if($subtitle)
                <p class="card-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="flex items-center gap-2 shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
    <div class="p-5">
        {{ $slot }}
    </div>
</div>
