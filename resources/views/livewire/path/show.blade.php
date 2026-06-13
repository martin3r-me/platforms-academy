<div class="p-6 space-y-6">
    <div>
        <a wire:navigate href="{{ route('academy.paths.index') }}" class="text-sm text-muted hover:underline">← Lernpfade</a>
        <h1 class="text-2xl font-semibold mt-2 d-flex items-center gap-2">
            @svg($path->icon ?: 'heroicon-o-map', 'w-6 h-6 text-muted')
            {{ $path->title }}
            @if($path->target_audience)
                <x-ui-badge variant="muted" size="sm">{{ $path->target_audience }}</x-ui-badge>
            @endif
        </h1>
        @if($path->description)
            <p class="text-sm text-muted mt-2">{{ $path->description }}</p>
        @endif

        <div class="mt-4">
            <div class="d-flex items-center justify-between text-sm text-muted mb-1">
                <span>{{ $summary['completed'] }} / {{ $summary['total'] }} abgeschlossen</span>
                <span>{{ $summary['pct'] }}%</span>
            </div>
            <div class="w-full bg-muted-10 rounded-full h-2">
                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $summary['pct'] }}%"></div>
            </div>
        </div>
    </div>

    @if($lessons->isEmpty())
        <div class="p-6 text-center bg-muted-5 border border-muted rounded-lg text-muted">
            Diesem Lernpfad sind noch keine Lessons zugeordnet.
        </div>
    @else
        <ol class="space-y-2">
            @foreach($lessons as $i => $lesson)
                <li>
                    <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $lesson->uuid]) }}"
                       class="d-flex items-center gap-3 p-4 bg-muted-5 hover:bg-muted-10 border border-muted rounded-lg">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-muted-10 d-flex items-center justify-center text-sm font-semibold">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="font-medium truncate">{{ $lesson->title }}</div>
                            <div class="text-xs text-muted truncate">{{ $lesson->topic?->title }}</div>
                        </div>
                        <div class="flex-shrink-0 d-flex items-center gap-2">
                            @if($lesson->estimated_minutes)
                                <span class="text-xs text-muted">{{ $lesson->estimated_minutes }} min</span>
                            @endif
                            @if(isset($completedSet[$lesson->id]))
                                @svg('heroicon-s-check-circle', 'w-5 h-5 text-emerald-500')
                            @else
                                @svg('heroicon-o-chevron-right', 'w-5 h-5 text-muted')
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
</div>
