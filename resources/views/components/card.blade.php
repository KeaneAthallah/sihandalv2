@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    @if($title || isset($actions) || isset($header))
        <div class="card-header">
            <div class="min-w-0">
                @if(isset($header))
                    {{ $header }}
                @else
                    <h3 class="card-title">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="card-subtitle">{{ $subtitle }}</p>
                    @endif
                @endif
            </div>
            @if(isset($actions))
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</div>
