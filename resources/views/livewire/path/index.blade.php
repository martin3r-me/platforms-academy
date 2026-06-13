<div>
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Lernpfade', 'href' => route('academy.paths.index')],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">

            <div>
                <h1 class="text-xl font-medium tracking-tight text-gray-900 dark:text-gray-100">Lernpfade</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kuratierte Reihenfolgen aus mehreren Lessons</p>
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
                                @if($path->target_audience)
                                    <x-ui-badge variant="muted" size="xs">{{ $path->target_audience }}</x-ui-badge>
                                @endif
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
    </x-ui-page-container>
</x-ui-page>
</div>
