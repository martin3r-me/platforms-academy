<div class="h-full">
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-medium tracking-tight text-gray-900 dark:text-gray-100">Academy</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lernpfade, Themen und Lessons für dein Team</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] p-4">
                    <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Themen</div>
                    <div class="text-2xl font-semibold mt-1 text-gray-900 dark:text-gray-100">{{ $topicsCount }}</div>
                </div>
                <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] p-4">
                    <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Veröffentlichte Lessons</div>
                    <div class="text-2xl font-semibold mt-1 text-gray-900 dark:text-gray-100">{{ $lessonsCount }}</div>
                </div>
                <div class="rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] p-4">
                    <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Von dir abgeschlossen</div>
                    <div class="text-2xl font-semibold mt-1 text-gray-900 dark:text-gray-100">{{ $completedCount }}</div>
                </div>
            </div>

            @if($continueLessons->isNotEmpty())
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Weitermachen</h2>
                    <div class="space-y-2">
                        @foreach($continueLessons as $lesson)
                            <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $lesson->uuid]) }}"
                               class="block p-4 rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] hover:bg-[var(--ui-muted-10)] transition">
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lesson->topic?->title }}</div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $lesson->title }}</div>
                                @if($lesson->summary)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $lesson->summary }}</div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Lernpfade</h2>
                    <a wire:navigate href="{{ route('academy.paths.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Alle ansehen →</a>
                </div>

                @if($paths->isEmpty())
                    <div class="p-6 text-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                        Noch keine Lernpfade veröffentlicht.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($paths as $path)
                            <a wire:navigate href="{{ route('academy.paths.show', ['uuid' => $path->uuid]) }}"
                               class="block p-5 rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] hover:bg-[var(--ui-muted-10)] transition">
                                <div class="flex items-center gap-2">
                                    @svg($path->icon ?: 'heroicon-o-map', 'w-5 h-5 text-gray-500 dark:text-gray-400')
                                    <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $path->title }}</h3>
                                </div>
                                @if($path->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">{{ $path->description }}</p>
                                @endif
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                        <span>{{ $path->lessons_count }} Lessons</span>
                                        <span>{{ $path->progress_pct }}%</span>
                                    </div>
                                    <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-2">
                                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $path->progress_pct }}%"></div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
