<div>
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Kurse', 'href' => route('academy.paths.index')],
        ]">
            <button @click="Alpine?.store('page') && (Alpine.store('page')['activityOpen'] = !Alpine.store('page')['activityOpen'])"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors">
                @svg('heroicon-o-chart-bar', 'w-4 h-4')
                <span class="hidden sm:inline">Aktivität</span>
            </button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Kurse" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-5 space-y-3">
                <div class="p-3 rounded-lg bg-black/[0.02] dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Kurse</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $paths->count() }}</span>
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-emerald-500/5 border border-emerald-500/15">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600 dark:text-gray-300">Dein Schnitt</span>
                        <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ $paths->isNotEmpty() ? round($paths->avg('progress_pct')) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">

            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100" style="font-family: var(--ui-font-mono);">Kurskatalog</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kuratierte Kurse aus mehreren Lektionen — schreib dich ein und tracke deinen Fortschritt</p>
            </div>

            {{-- Kategorie-Filter --}}
            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <a wire:navigate href="{{ route('academy.paths.index') }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full border text-xs transition {{ !$activeCategory ? 'bg-gray-900 text-white border-gray-900 dark:bg-gray-100 dark:text-gray-900 dark:border-gray-100' : 'border-[var(--ui-border)] bg-[var(--ui-surface)] text-gray-600 dark:text-gray-300 hover:border-gray-400' }}"
                       style="font-family: var(--ui-font-mono);">Alle</a>
                    @foreach($categories as $cat)
                        <a wire:navigate href="{{ route('academy.paths.index', ['category' => $cat->slug]) }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full border text-xs transition {{ $activeCategory && $activeCategory->id === $cat->id ? 'bg-gray-900 text-white border-gray-900 dark:bg-gray-100 dark:text-gray-900 dark:border-gray-100' : 'border-[var(--ui-border)] bg-[var(--ui-surface)] text-gray-600 dark:text-gray-300 hover:border-gray-400' }}"
                           style="font-family: var(--ui-font-mono);">
                            <span class="w-2 h-2 rounded-full" style="background: {{ $cat->color() }};"></span>
                            {{ $cat->title }}
                            <span class="opacity-50">{{ $cat->paths_count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($paths->isEmpty())
                <div class="p-6 text-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                    @if($activeCategory)
                        In dieser Kategorie sind noch keine Kurse veröffentlicht.
                    @else
                        Noch keine Kurse veröffentlicht.
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($paths as $path)
                        <a wire:navigate href="{{ route('academy.paths.show', ['uuid' => $path->uuid]) }}"
                           class="group rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] overflow-hidden flex flex-col shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                            @include('academy::partials.course-cover', ['path' => $path, 'size' => 'card'])
                            <div class="p-4 flex flex-col gap-2 flex-1">
                                @if($path->category)
                                    <span class="text-[10px] font-semibold uppercase tracking-wider" style="font-family: var(--ui-font-mono); color: {{ $path->coverColor() }};">{{ $path->category->title }}</span>
                                @endif
                                <h3 class="font-semibold text-[15px] text-gray-900 dark:text-gray-100 leading-tight">{{ $path->title }}</h3>
                                @if($path->description)
                                    <p class="text-[13px] text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2">{{ $path->description }}</p>
                                @endif

                                <div class="mt-auto pt-2">
                                    @if(isset($enrolledSet[$path->id]))
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-[var(--ui-muted-10)] rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $path->progress_pct }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400" style="font-family: var(--ui-font-mono);">{{ $path->progress_pct }}%</span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);">
                                                {{ $path->lessons_count }} {{ $path->lessons_count == 1 ? 'Lektion' : 'Lektionen' }}@if($path->levelLabel()) · {{ $path->levelLabel() }}@endif
                                            </span>
                                            <span class="text-[11px] font-semibold text-[var(--ui-primary)] opacity-0 group-hover:opacity-100 transition-opacity" style="font-family: var(--ui-font-mono);">Ansehen →</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
