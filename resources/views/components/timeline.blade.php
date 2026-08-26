@props([
    'steps' => [],
    'currentStep' => 3,
])

<div class="space-y-0">
    @foreach($steps as $index => $step)
        @php
            $stepNumber = $index + 1;
            $isCompleted = $stepNumber < $currentStep;
            $isCurrent = $stepNumber === $currentStep;
            $isPending = $stepNumber > $currentStep;
        @endphp
        <div class="flex items-start gap-3">
            <div class="flex flex-col items-center">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                    @if($isCompleted) bg-primary text-white
                    @elseif($isCurrent) bg-primary text-white ring-4 ring-primary/20
                    @else bg-slate-100 text-slate-400 border-2 border-slate-200
                    @endif">
                    @if($isCompleted)
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    @else
                        {{ $stepNumber }}
                    @endif
                </div>
                @if(!$loop->last)
                    <div class="w-0.5 h-7 mt-1 {{ $isCompleted ? 'bg-primary' : 'bg-slate-200' }}"></div>
                @endif
            </div>

            <div class="pb-5 pt-0.5">
                <p class="text-sm font-medium {{ ($isCurrent ? 'text-primary' : ($isCompleted ? 'text-slate-700' : 'text-slate-400')) }}">
                    {{ $step }}
                </p>
                @if($isCurrent)
                    <p class="text-xs text-slate-400 mt-0.5">Sedang berlangsung</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
