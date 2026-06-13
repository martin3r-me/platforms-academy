<div>
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Themen', 'href' => route('academy.topics.index')],
            ['label' => $topic->title, 'href' => route('academy.topics.show', ['uuid' => $topic->uuid])],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">

            <div>
                <h1 class="text-xl font-medium tracking-tight text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    @svg($topic->icon ?: 'heroicon-o-folder', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                    {{ $topic->title }}
                </h1>
                @if($topic->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $topic->description }}</p>
                @endif
            </div>

            @if($lessons->isEmpty())
                <div class="p-6 text-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                    Noch keine veröffentlichten Lessons in diesem Thema.
                </div>
            @else
                <ol class="space-y-2">
                    @foreach($lessons as $i => $lesson)
                        <li>
                            <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $lesson->uuid]) }}"
                               class="flex items-center gap-3 p-4 rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] hover:bg-[var(--ui-muted-10)] transition">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--ui-muted-10)] flex items-center justify-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $i + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $lesson->title }}</div>
                                    @if($lesson->summary)
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $lesson->summary }}</div>
                                    @endif
                                </div>
                                <div class="flex-shrink-0 flex items-center gap-2">
                                    @if($lesson->estimated_minutes)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $lesson->estimated_minutes }} min</span>
                                    @endif
                                    @if(isset($completedSet[$lesson->id]))
                                        @svg('heroicon-s-check-circle', 'w-5 h-5 text-emerald-500')
                                    @else
                                        @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-400')
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
