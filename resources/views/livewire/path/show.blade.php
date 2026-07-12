<div class="h-full">
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar :title="$path->title" icon="heroicon-o-rectangle-stack" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Kurse', 'href' => route('academy.paths.index')],
            ['label' => $path->code ? $path->code : $path->title, 'href' => route('academy.paths.show', ['uuid' => $path->uuid])],
        ]">
            <button @click="Alpine?.store('page') && (Alpine.store('page')['activityOpen'] = !Alpine.store('page')['activityOpen'])"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-sm rounded-lg text-[var(--ui-muted)] hover:text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] transition-colors">
                @svg('heroicon-o-information-circle', 'w-4 h-4')
                <span class="hidden sm:inline">Info</span>
            </button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Lektionen" icon="heroicon-o-list-bullet" width="w-72" :defaultOpen="true">
            <nav class="p-3 space-y-1">
                @forelse($lessons as $i => $lesson)
                    @php
                        $isDone = isset($completedSet[$lesson->id]);
                        $isCurrent = $resumeLesson && $resumeLesson->id === $lesson->id && !$isDone;
                    @endphp
                    <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $lesson->uuid]) }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $isCurrent ? 'bg-[var(--ui-primary-5)] text-[var(--ui-primary)] font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-[var(--ui-muted-5)]' }}">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full text-[10px] flex items-center justify-center {{ $isDone ? 'bg-emerald-500 text-white' : 'bg-[var(--ui-muted-10)] text-gray-500' }}" style="font-family: var(--ui-font-mono);">
                            @if($isDone) @svg('heroicon-s-check', 'w-3 h-3') @else {{ $i + 1 }} @endif
                        </span>
                        <span class="flex-1 truncate">{{ $lesson->title }}</span>
                    </a>
                @empty
                    <div class="px-3 py-2 text-xs text-gray-400">Noch keine Lektionen.</div>
                @endforelse
            </nav>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Kurs" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-5 space-y-5">
                <div>
                    <h3 class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Meta</h3>
                    <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400">
                        @if($path->code)
                            <div><span class="font-medium text-gray-700 dark:text-gray-300">Code:</span> {{ $path->code }}</div>
                        @endif
                        @if($path->category)
                            <div><span class="font-medium text-gray-700 dark:text-gray-300">Kategorie:</span> {{ $path->category->title }}</div>
                        @endif
                        @if($path->levelLabel())
                            <div><span class="font-medium text-gray-700 dark:text-gray-300">Level:</span> {{ $path->levelLabel() }}</div>
                        @endif
                        @if($path->target_audience)
                            <div><span class="font-medium text-gray-700 dark:text-gray-300">Für wen:</span> {{ $path->target_audience }}</div>
                        @endif
                        <div><span class="font-medium text-gray-700 dark:text-gray-300">Status:</span> {{ $path->status }}</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- Hero-Cover --}}
            <div class="rounded-2xl overflow-hidden border border-[var(--ui-border)] shadow-sm">
                @include('academy::partials.course-cover', ['path' => $path, 'size' => 'hero'])
            </div>

            @if($path->description)
                <p class="text-[15px] leading-relaxed text-gray-600 dark:text-gray-300 max-w-2xl">{{ $path->description }}</p>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-6 items-start">

                {{-- Lektionen mit beschreibenden Texten --}}
                <div class="order-2 lg:order-1">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4"
                        style="font-family: var(--ui-font-mono);">
                        {{ $summary['total'] }} {{ $summary['total'] == 1 ? 'Lektion' : 'Lektionen' }}
                    </h2>

                    @if($lessons->isEmpty())
                        <div class="p-6 text-center rounded-xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                            Diesem Kurs sind noch keine Lektionen zugeordnet.
                        </div>
                    @else
                        <ol class="divide-y divide-[var(--ui-border)] border-y border-[var(--ui-border)]">
                            @foreach($lessons as $i => $lesson)
                                @php
                                    $isDone = isset($completedSet[$lesson->id]);
                                    $isCurrent = $resumeLesson && $resumeLesson->id === $lesson->id && !$isDone;
                                @endphp
                                <li>
                                    <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $lesson->uuid]) }}"
                                       class="group flex gap-4 py-4 hover:bg-[var(--ui-muted-5)] -mx-3 px-3 rounded-lg transition">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-sm font-semibold
                                            @if($isDone) bg-emerald-500/15 text-emerald-600 dark:text-emerald-400
                                            @elseif($isCurrent) bg-[var(--ui-primary)] text-white
                                            @else bg-[var(--ui-muted-5)] border border-[var(--ui-border)] text-gray-500 dark:text-gray-400 @endif"
                                            style="font-family: var(--ui-font-mono);">
                                            @if($isDone) @svg('heroicon-s-check', 'w-4 h-4') @else {{ $i + 1 }} @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-semibold text-[15px] text-gray-900 dark:text-gray-100">{{ $lesson->title }}</span>
                                                @if($isCurrent)
                                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--ui-primary-10)] text-[var(--ui-primary)]"
                                                          style="font-family: var(--ui-font-mono);">Aktuell</span>
                                                @endif
                                            </div>
                                            @if($lesson->summary)
                                                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed line-clamp-2">{{ $lesson->summary }}</p>
                                            @endif
                                        </div>
                                        @if($lesson->estimated_minutes)
                                            <div class="flex-shrink-0 text-[11px] text-gray-400 whitespace-nowrap pt-1" style="font-family: var(--ui-font-mono);">{{ $lesson->estimated_minutes }} min</div>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>

                {{-- Enroll / Fortschritt Sidebar --}}
                <aside class="order-1 lg:order-2 lg:sticky lg:top-4">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] p-5 space-y-4">

                        @if($enrollment)
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1" style="font-family: var(--ui-font-mono);">Dein Fortschritt</div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-bold tracking-tight text-gray-900 dark:text-gray-100" style="font-family: var(--ui-font-mono);">{{ $summary['pct'] }}</span>
                                    <span class="text-lg text-gray-400" style="font-family: var(--ui-font-mono);">%</span>
                                </div>
                            </div>
                            <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-2">
                                <div class="h-2 rounded-full bg-emerald-500 transition-all" style="width: {{ $summary['pct'] }}%"></div>
                            </div>

                            @if($enrollment->isCompleted())
                                <div class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    @svg('heroicon-s-check-badge', 'w-5 h-5') Abgeschlossen
                                </div>
                            @elseif($resumeLesson)
                                <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $resumeLesson->uuid]) }}"
                                   class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg bg-[var(--ui-primary)] text-white text-sm font-semibold hover:opacity-90 transition"
                                   style="box-shadow: 0 6px 16px -6px rgba(79,70,229,.7);">
                                    Weiterlernen
                                    @svg('heroicon-s-arrow-right', 'w-4 h-4')
                                </a>
                            @endif
                        @else
                            <div>
                                <div class="text-sm text-gray-600 dark:text-gray-300 mb-1">Bereit loszulegen?</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Schreib dich ein, um deinen Fortschritt zu tracken.</div>
                            </div>
                            <button wire:click="enroll"
                                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-lg bg-[var(--ui-primary)] text-white text-sm font-semibold hover:opacity-90 transition"
                                    style="box-shadow: 0 6px 16px -6px rgba(79,70,229,.7);">
                                @svg('heroicon-o-plus', 'w-4 h-4')
                                In Kurs einschreiben
                            </button>
                        @endif

                        <div class="h-px bg-[var(--ui-border)]"></div>

                        <div class="space-y-2 text-[13px]">
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Lektionen</span><span class="font-medium text-gray-900 dark:text-gray-100" style="font-family: var(--ui-font-mono);">{{ $summary['completed'] }} / {{ $summary['total'] }}</span></div>
                            @if($path->levelLabel())
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Level</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $path->levelLabel() }}</span></div>
                            @endif
                            @if($path->code)
                                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Code</span><span class="font-medium text-gray-900 dark:text-gray-100" style="font-family: var(--ui-font-mono);">{{ $path->code }}</span></div>
                            @endif
                        </div>

                        @if($path->target_audience)
                            <div class="h-px bg-[var(--ui-border)]"></div>
                            <div class="text-[13px] text-gray-600 dark:text-gray-300 leading-relaxed">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">Für wen?</span><br>{{ $path->target_audience }}
                            </div>
                        @endif

                        @if($enrollment)
                            <button wire:click="drop"
                                    wire:confirm="Kurs wirklich verlassen? Dein Lektions-Fortschritt bleibt erhalten."
                                    class="w-full text-center text-xs text-gray-400 hover:text-red-500 transition pt-1">
                                Kurs verlassen
                            </button>
                        @endif
                    </div>
                </aside>

            </div>
        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
