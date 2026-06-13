<div class="p-6 max-w-3xl mx-auto space-y-6">
    <div>
        <a wire:navigate href="{{ route('academy.topics.show', ['uuid' => $lesson->topic->uuid]) }}"
           class="text-sm text-muted hover:underline">← {{ $lesson->topic->title }}</a>
        <h1 class="text-2xl font-semibold mt-2">{{ $lesson->title }}</h1>
        @if($lesson->summary)
            <p class="text-sm text-muted mt-2">{{ $lesson->summary }}</p>
        @endif
        @if($lesson->estimated_minutes)
            <div class="text-xs text-muted mt-1">⏱ ca. {{ $lesson->estimated_minutes }} Minuten</div>
        @endif
    </div>

    <article class="prose dark:prose-invert max-w-none">
        {!! $renderedContent !!}
    </article>

    <div class="d-flex items-center justify-between gap-3 pt-6 border-t border-muted">
        <div>
            @if($prev)
                <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $prev->uuid]) }}"
                   class="text-sm text-muted hover:underline">← {{ $prev->title }}</a>
            @endif
        </div>

        <div>
            @if($isCompleted)
                <x-ui-button wire:click="reopen" variant="muted" size="sm">
                    @svg('heroicon-s-check-circle', 'w-4 h-4 mr-1 inline')
                    Abgeschlossen — erneut öffnen
                </x-ui-button>
            @else
                <x-ui-button wire:click="markComplete" variant="primary" size="sm">
                    Als erledigt markieren
                </x-ui-button>
            @endif
        </div>

        <div class="text-right">
            @if($next)
                <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $next->uuid]) }}"
                   class="text-sm text-muted hover:underline">{{ $next->title }} →</a>
            @endif
        </div>
    </div>
</div>
