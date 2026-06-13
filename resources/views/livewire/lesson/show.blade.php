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
        ]">
            <button @click="Alpine?.store('page') && (Alpine.store('page')['activityOpen'] = !Alpine.store('page')['activityOpen'])"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors">
                @svg('heroicon-o-information-circle', 'w-4 h-4')
                <span class="hidden sm:inline">Info</span>
            </button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="{{ $lesson->topic->title }}" icon="heroicon-o-list-bullet" width="w-72" :defaultOpen="true">
            <nav class="p-3 space-y-1">
                @foreach($topicLessons as $i => $tl)
                    <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $tl->uuid]) }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                              {{ $tl->id === $lesson->id
                                 ? 'bg-[var(--ui-primary-5)] text-[var(--ui-primary)] font-medium'
                                 : 'text-gray-700 dark:text-gray-300 hover:bg-[var(--ui-muted-5)]' }}">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full text-[10px] flex items-center justify-center
                                     {{ isset($completedSet[$tl->id]) ? 'bg-emerald-500 text-white' : 'bg-[var(--ui-muted-10)] text-gray-500' }}">
                            @if(isset($completedSet[$tl->id]))
                                @svg('heroicon-s-check', 'w-3 h-3')
                            @else
                                {{ $i + 1 }}
                            @endif
                        </span>
                        <span class="flex-1 truncate">{{ $tl->title }}</span>
                    </a>
                @endforeach
            </nav>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Lesson" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-5 space-y-5">
                <div>
                    <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Status</h3>
                    @if($isCompleted)
                        <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center gap-2">
                            @svg('heroicon-s-check-circle', 'w-5 h-5 text-emerald-500')
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Abgeschlossen</span>
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-amber-500/5 border border-amber-500/15 flex items-center gap-2">
                            @svg('heroicon-o-clock', 'w-5 h-5 text-amber-500')
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-300">In Bearbeitung</span>
                        </div>
                    @endif
                </div>

                <div>
                    <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Thema</h3>
                    <a wire:navigate href="{{ route('academy.topics.show', ['uuid' => $lesson->topic->uuid]) }}"
                       class="block p-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.03] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition">
                        <div class="flex items-center gap-2">
                            @svg($lesson->topic->icon ?: 'heroicon-o-folder', 'w-4 h-4 text-gray-500')
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $lesson->topic->title }}</span>
                        </div>
                    </a>
                </div>

                @if($pathMemberships->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Lernpfade</h3>
                        <div class="space-y-1">
                            @foreach($pathMemberships as $p)
                                <a wire:navigate href="{{ route('academy.paths.show', ['uuid' => $p->uuid]) }}"
                                   class="block p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.03] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition">
                                    <div class="flex items-center gap-2">
                                        @svg($p->icon ?: 'heroicon-o-map', 'w-4 h-4 text-gray-500')
                                        <span class="text-sm text-gray-900 dark:text-gray-100 truncate">{{ $p->title }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Meta</h3>
                    <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400">
                        @if($lesson->estimated_minutes)
                            <div><span class="font-medium text-gray-700 dark:text-gray-300">Dauer:</span> ca. {{ $lesson->estimated_minutes }} min</div>
                        @endif
                        <div><span class="font-medium text-gray-700 dark:text-gray-300">Status:</span> {{ $lesson->status }}</div>
                        <div><span class="font-medium text-gray-700 dark:text-gray-300">Aktualisiert:</span> {{ $lesson->updated_at?->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
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
