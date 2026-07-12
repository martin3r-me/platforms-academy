<div>
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Bibliothek', 'href' => route('academy.topics.index')],
        ]">
            <button @click="Alpine?.store('page') && (Alpine.store('page')['activityOpen'] = !Alpine.store('page')['activityOpen'])"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors">
                @svg('heroicon-o-chart-bar', 'w-4 h-4')
                <span class="hidden sm:inline">Aktivität</span>
            </button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Bibliothek" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-5 space-y-3">
                <div class="p-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Themen</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $topics->count() }}</span>
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Lektionen gesamt</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $lessonsTotal }}</span>
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-emerald-500/5 border border-emerald-500/15">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-300">Von dir abgeschlossen</span>
                        <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $completedTotal }}</span>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100" style="font-family: var(--ui-font-mono);">Bibliothek</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">
                    Stöbere frei nach Thema und pick dir einzelne Lektionen heraus. Wenn du lieber kuratiert lernst,
                    <a wire:navigate href="{{ route('academy.paths.index') }}" class="text-[var(--ui-primary)] font-medium hover:underline">geh über die Kurse</a>.
                </p>
            </div>

            @if($topics->isEmpty())
                <div class="p-6 text-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                    Noch keine Themen angelegt.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($topics as $topic)
                        <a wire:navigate href="{{ route('academy.topics.show', ['uuid' => $topic->uuid]) }}"
                           class="group rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 flex flex-col shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[var(--ui-muted-5)] border border-[var(--ui-border)] flex items-center justify-center text-gray-500 dark:text-gray-400">
                                    @svg($topic->icon ?: 'heroicon-o-folder', 'w-5 h-5')
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-[15px] text-gray-900 dark:text-gray-100 leading-tight">{{ $topic->title }}</h3>
                                    <div class="text-[11px] text-gray-400 mt-0.5" style="font-family: var(--ui-font-mono);">
                                        {{ $topic->lesson_total }} {{ $topic->lesson_total === 1 ? 'Lektion' : 'Lektionen' }}
                                    </div>
                                </div>
                            </div>

                            @if($topic->description)
                                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-3 leading-relaxed line-clamp-3">{{ $topic->description }}</p>
                            @endif

                            @if($topic->lesson_total > 0)
                                <div class="mt-4 pt-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-[var(--ui-muted-10)] rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $topic->progress_pct }}%"></div>
                                        </div>
                                        <span class="text-[11px] font-semibold {{ $topic->progress_pct > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}" style="font-family: var(--ui-font-mono);">{{ $topic->lesson_done }}/{{ $topic->lesson_total }}</span>
                                    </div>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
