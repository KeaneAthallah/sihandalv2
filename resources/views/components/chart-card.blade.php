@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden']) }}>
    <div class="px-5 lg:px-6 py-4 border-b border-slate-100">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-slate-800 tracking-tight">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
    <div class="p-5 lg:p-6">
        {{ $slot }}
    </div>
</div>
