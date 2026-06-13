<div class="p-6 space-y-4">
    <div>
        <h1 class="text-2xl font-semibold">Themen</h1>
        <p class="text-sm text-muted">Themen-Cluster mit den dazugehörigen Lessons</p>
    </div>

    @if($topics->isEmpty())
        <div class="p-6 text-center bg-muted-5 border border-muted rounded-lg text-muted">
            Noch keine Themen angelegt.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($topics as $topic)
                <a wire:navigate href="{{ route('academy.topics.show', ['uuid' => $topic->uuid]) }}"
                   class="block p-5 bg-muted-5 hover:bg-muted-10 border border-muted rounded-lg">
                    <div class="d-flex items-center gap-2">
                        @svg($topic->icon ?: 'heroicon-o-folder', 'w-5 h-5 text-muted')
                        <h3 class="font-semibold">{{ $topic->title }}</h3>
                    </div>
                    @if($topic->description)
                        <p class="text-sm text-muted mt-2 line-clamp-3">{{ $topic->description }}</p>
                    @endif
                    <div class="mt-3 text-xs text-muted">
                        {{ $topic->published_lessons_count }} {{ $topic->published_lessons_count === 1 ? 'Lesson' : 'Lessons' }}
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
