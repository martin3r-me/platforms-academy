<div class="p-6 space-y-6">
    <div class="d-flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Academy</h1>
            <p class="text-sm text-muted">Lernpfade, Themen und Lessons für dein Team</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-muted-5 border border-muted rounded-lg p-4">
            <div class="text-xs uppercase text-muted">Themen</div>
            <div class="text-2xl font-semibold mt-1">{{ $topicsCount }}</div>
        </div>
        <div class="bg-muted-5 border border-muted rounded-lg p-4">
            <div class="text-xs uppercase text-muted">Veröffentlichte Lessons</div>
            <div class="text-2xl font-semibold mt-1">{{ $lessonsCount }}</div>
        </div>
        <div class="bg-muted-5 border border-muted rounded-lg p-4">
            <div class="text-xs uppercase text-muted">Von dir abgeschlossen</div>
            <div class="text-2xl font-semibold mt-1">{{ $completedCount }}</div>
        </div>
    </div>

    @if($continueLessons->isNotEmpty())
        <div>
            <h2 class="text-lg font-semibold mb-3">Weitermachen</h2>
            <div class="space-y-2">
                @foreach($continueLessons as $lesson)
                    <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $lesson->uuid]) }}"
                       class="block p-4 bg-muted-5 hover:bg-muted-10 border border-muted rounded-lg">
                        <div class="text-xs text-muted">{{ $lesson->topic?->title }}</div>
                        <div class="font-medium">{{ $lesson->title }}</div>
                        @if($lesson->summary)
                            <div class="text-sm text-muted mt-1 line-clamp-2">{{ $lesson->summary }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <div class="d-flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Lernpfade</h2>
            <a wire:navigate href="{{ route('academy.paths.index') }}" class="text-sm text-muted hover:underline">Alle ansehen →</a>
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
</div>
