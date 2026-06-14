<div>
{{-- Highlight.js: Syntax-Highlighting für Code-Blocks --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.10.0/build/styles/github-dark.min.css">
<script defer src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.10.0/build/highlight.min.js"></script>

{{-- Academy-Reader-Styles: Callouts, Code-Blocks, Reading-Polish --}}
<style>
    /* === Callouts / GitHub-Alerts === */
    .academy-alert {
        margin: 1.5rem 0;
        padding: 0.875rem 1rem;
        border-left: 4px solid;
        border-radius: 0.5rem;
        background: var(--ui-muted-5);
    }
    .academy-alert-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .academy-alert-icon { width: 1.125rem; height: 1.125rem; flex-shrink: 0; }
    .academy-alert-body {
        color: var(--ui-secondary);
        font-size: 0.9375rem;
        line-height: 1.6;
    }
    .academy-alert-body > *:first-child { margin-top: 0; }
    .academy-alert-body > *:last-child { margin-bottom: 0; }

    .academy-alert-info     { border-left-color: #3b82f6; }
    .academy-alert-tip      { border-left-color: #10b981; }
    .academy-alert-warning  { border-left-color: #f59e0b; }
    .academy-alert-note     { border-left-color: #6b7280; }
    .academy-alert-important{ border-left-color: #d946ef; }
    .academy-alert-caution  { border-left-color: #ef4444; }

    .academy-alert-info     .academy-alert-label { color: #1d4ed8; }
    .academy-alert-tip      .academy-alert-label { color: #047857; }
    .academy-alert-warning  .academy-alert-label { color: #b45309; }
    .academy-alert-note     .academy-alert-label { color: #374151; }
    .academy-alert-important .academy-alert-label { color: #a21caf; }
    .academy-alert-caution  .academy-alert-label { color: #b91c1c; }

    .dark .academy-alert-info     .academy-alert-label { color: #93c5fd; }
    .dark .academy-alert-tip      .academy-alert-label { color: #6ee7b7; }
    .dark .academy-alert-warning  .academy-alert-label { color: #fcd34d; }
    .dark .academy-alert-note     .academy-alert-label { color: #d1d5db; }
    .dark .academy-alert-important .academy-alert-label { color: #f0abfc; }
    .dark .academy-alert-caution  .academy-alert-label { color: #fca5a5; }

    /* === Typography: explizite Hierarchie ohne Verlass auf prose-Plugin === */
    .academy-lesson-content {
        color: var(--ui-secondary);
        font-size: 1rem;
        line-height: 1.7;
    }
    .academy-lesson-content > * + * { margin-top: 1em; }

    .academy-lesson-content h1 {
        margin-top: 0;
        margin-bottom: 0.75em;
        font-size: 1.875rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--ui-primary);
    }
    .academy-lesson-content h2 {
        margin-top: 2.5em;
        margin-bottom: 0.75em;
        padding-bottom: 0.4em;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.3;
        color: var(--ui-primary);
        border-bottom: 1px solid var(--ui-border);
    }
    .academy-lesson-content h3 {
        margin-top: 2em;
        margin-bottom: 0.5em;
        font-size: 1.125rem;
        font-weight: 600;
        line-height: 1.4;
        color: var(--ui-primary);
    }
    .academy-lesson-content h4 {
        margin-top: 1.5em;
        margin-bottom: 0.4em;
        font-size: 1rem;
        font-weight: 600;
        color: var(--ui-primary);
    }
    .academy-lesson-content p {
        margin: 0.75em 0;
        line-height: 1.7;
    }
    .academy-lesson-content strong {
        color: var(--ui-primary);
        font-weight: 600;
    }
    .academy-lesson-content em { font-style: italic; }

    /* Listen — explizite Bullets/Numbers, klare Einrueckung */
    .academy-lesson-content ul,
    .academy-lesson-content ol {
        margin: 1em 0;
        padding-left: 1.5em;
    }
    .academy-lesson-content ul { list-style: disc; }
    .academy-lesson-content ol { list-style: decimal; }
    .academy-lesson-content li {
        margin: 0.4em 0;
        padding-left: 0.375em;
    }
    .academy-lesson-content li::marker {
        color: var(--ui-muted);
        font-weight: 600;
    }
    .academy-lesson-content li > p { margin: 0.25em 0; }
    .academy-lesson-content li > ul,
    .academy-lesson-content li > ol { margin: 0.4em 0; }

    /* Tables */
    .academy-lesson-content table {
        width: 100%;
        margin: 1.5em 0;
        border-collapse: collapse;
        font-size: 0.9375rem;
    }
    .academy-lesson-content thead {
        background: var(--ui-muted-5);
        border-bottom: 2px solid var(--ui-border);
    }
    .academy-lesson-content th {
        padding: 0.625em 1em;
        text-align: left;
        font-weight: 600;
        color: var(--ui-primary);
    }
    .academy-lesson-content td {
        padding: 0.625em 1em;
        border-top: 1px solid var(--ui-border);
    }

    /* Links */
    .academy-lesson-content a {
        color: #2563eb;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .academy-lesson-content a:hover { color: #1d4ed8; }
    .dark .academy-lesson-content a { color: #60a5fa; }
    .dark .academy-lesson-content a:hover { color: #93c5fd; }

    /* Blockquotes (echte, nicht Callouts) */
    .academy-lesson-content blockquote {
        margin: 1.5em 0;
        padding: 0.5em 1em;
        border-left: 3px solid var(--ui-muted-10);
        color: var(--ui-muted);
        font-style: italic;
    }

    /* Horizontale Trenner */
    .academy-lesson-content hr {
        margin: 2.5em 0;
        border: none;
        border-top: 1px solid var(--ui-border);
    }

    /* === Code-Blocks === */
    .academy-lesson-content pre {
        margin: 1.25em 0;
        background: #0d1117;
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
        overflow-x: auto;
        font-size: 0.875rem;
        line-height: 1.6;
    }
    .academy-lesson-content pre code {
        background: transparent;
        padding: 0;
        color: #c9d1d9;
        font-size: inherit;
    }
    .academy-lesson-content :not(pre) > code {
        background: var(--ui-muted-10);
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-size: 0.875em;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
</style>

{{-- Highlight.js init: einmal beim Laden + bei jeder Livewire-Navigation --}}
<script>
    (function() {
        function initAcademyHighlight() {
            if (typeof hljs !== 'undefined' && typeof hljs.highlightAll === 'function') {
                hljs.highlightAll();
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAcademyHighlight);
        } else {
            // already loaded — re-init (e.g. via wire:navigate)
            setTimeout(initAcademyHighlight, 50);
        }
        document.addEventListener('livewire:navigated', initAcademyHighlight);
    })();
</script>

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

            <article class="academy-lesson-content">
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
