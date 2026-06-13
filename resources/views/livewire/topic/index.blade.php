<div>
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Themen', 'href' => route('academy.topics.index')],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">

            <div>
                <h1 class="text-xl font-medium tracking-tight text-gray-900 dark:text-gray-100">Themen</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Themen-Cluster mit den dazugehörigen Lessons</p>
            </div>

            @if($topics->isEmpty())
                <div class="p-6 text-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                    Noch keine Themen angelegt.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($topics as $topic)
                        <a wire:navigate href="{{ route('academy.topics.show', ['uuid' => $topic->uuid]) }}"
                           class="block p-5 rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] hover:bg-[var(--ui-muted-10)] transition">
                            <div class="flex items-center gap-2">
                                @svg($topic->icon ?: 'heroicon-o-folder', 'w-5 h-5 text-gray-500 dark:text-gray-400')
                                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $topic->title }}</h3>
                            </div>
                            @if($topic->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-3">{{ $topic->description }}</p>
                            @endif
                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $topic->published_lessons_count }} {{ $topic->published_lessons_count === 1 ? 'Lesson' : 'Lessons' }}
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
