<div class="p-6 space-y-4">
    <div>
        <h1 class="text-2xl font-semibold">Lernpfade</h1>
        <p class="text-sm text-muted">Kuratierte Reihenfolgen aus mehreren Lessons</p>
    </div>

    @if($paths->isEmpty())
        <div class="p-6 text-center bg-muted-5 border border-muted rounded-lg text-muted">
            Noch keine Lernpfade veröffentlicht.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($paths as $path)
                <a wire:navigate href="{{ route('academy.paths.show', ['uuid' => $path->uuid]) }}"
                   class="block p-5 bg-muted-5 hover:bg-muted-10 border border-muted rounded-lg">
                    <div class="d-flex items-center gap-2">
                        @svg($path->icon ?: 'heroicon-o-map', 'w-5 h-5 text-muted')
                        <h3 class="font-semibold">{{ $path->title }}</h3>
                        @if($path->target_audience)
                            <x-ui-badge variant="muted" size="xs">{{ $path->target_audience }}</x-ui-badge>
                        @endif
                    </div>
                    @if($path->description)
                        <p class="text-sm text-muted mt-2 line-clamp-2">{{ $path->description }}</p>
                    @endif
                    <div class="mt-4">
                        <div class="d-flex items-center justify-between text-xs text-muted mb-1">
                            <span>{{ $path->lessons_count }} Lessons</span>
                            <span>{{ $path->progress_pct }}%</span>
                        </div>
                        <div class="w-full bg-muted-10 rounded-full h-2">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $path->progress_pct }}%"></div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
