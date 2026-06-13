<div>
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Themen', 'href' => route('academy.topics.index')],
            ['label' => $lesson->topic->title, 'href' => route('academy.topics.show', ['uuid' => $lesson->topic->uuid])],
            ['label' => $lesson->title, 'href' => route('academy.lessons.show', ['uuid' => $lesson->uuid])],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="max-w-3xl mx-auto space-y-6">

            <div>
                <h1 class="text-2xl font-medium tracking-tight text-gray-900 dark:text-gray-100">{{ $lesson->title }}</h1>
                @if($lesson->summary)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ $lesson->summary }}</p>
                @endif
                @if($lesson->estimated_minutes)
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">⏱ ca. {{ $lesson->estimated_minutes }} Minuten</div>
                @endif
            </div>

            <article class="prose dark:prose-invert max-w-none">
                {!! $renderedContent !!}
            </article>

            <div class="flex items-center justify-between gap-3 pt-6 border-t border-[var(--ui-border)]">
                <div class="flex-1">
                    @if($prev)
                        <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $prev->uuid]) }}"
                           class="text-sm text-gray-500 dark:text-gray-400 hover:underline">← {{ $prev->title }}</a>
                    @endif
                </div>

                <div class="flex-shrink-0">
                    @if($isCompleted)
                        <x-ui-button wire:click="reopen" variant="secondary-outline" size="sm">
                            @svg('heroicon-s-check-circle', 'w-4 h-4 mr-1 inline text-emerald-500')
                            Abgeschlossen
                        </x-ui-button>
                    @else
                        <x-ui-button wire:click="markComplete" variant="primary" size="sm">
                            Als erledigt markieren
                        </x-ui-button>
                    @endif
                </div>

                <div class="flex-1 text-right">
                    @if($next)
                        <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $next->uuid]) }}"
                           class="text-sm text-gray-500 dark:text-gray-400 hover:underline">{{ $next->title }} →</a>
                    @endif
                </div>
            </div>
        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
