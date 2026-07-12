<div>
    <div x-show="!collapsed" class="p-3 text-sm italic text-[var(--ui-secondary)] uppercase border-b border-[var(--ui-border)] mb-2">
        Academy
    </div>

    <x-ui-sidebar-list label="Navigation">
        <x-ui-sidebar-item :href="route('academy.dashboard')" :active="request()->routeIs('academy.dashboard')">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Dashboard</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('academy.paths.index')" :active="request()->routeIs('academy.paths.index')">
            @svg('heroicon-o-rectangle-stack', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Kurse</span>
            <span class="ml-1.5 text-[10px] text-[var(--ui-muted)]">geführt</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('academy.topics.index')" :active="request()->routeIs('academy.topics.*')">
            @svg('heroicon-o-book-open', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Bibliothek</span>
            <span class="ml-1.5 text-[10px] text-[var(--ui-muted)]">frei</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    @if($courses->isNotEmpty())
        <x-ui-sidebar-list label="Meine Kurse">
            @foreach($courses as $course)
                <x-ui-sidebar-item :href="route('academy.paths.show', ['uuid' => $course['uuid']])" :active="request()->is('*/academy/paths/' . $course['uuid'])">
                    @svg($course['icon'] ?: 'heroicon-o-rectangle-stack', 'w-4 h-4 text-[var(--ui-secondary)]')
                    <span class="ml-2 text-sm truncate">{{ $course['title'] }}</span>
                    <x-slot name="trailing">
                        @if($course['completed'])
                            @svg('heroicon-s-check-circle', 'w-4 h-4 text-emerald-500')
                        @else
                            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">{{ $course['pct'] }}%</span>
                        @endif
                    </x-slot>
                </x-ui-sidebar-item>
            @endforeach
        </x-ui-sidebar-list>
    @endif

    <div x-show="collapsed" class="px-2 py-2 border-b border-[var(--ui-border)]">
        <div class="flex flex-col gap-2">
            <a href="{{ route('academy.dashboard') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] {{ request()->routeIs('academy.dashboard') ? 'bg-[var(--ui-primary-5)] text-[var(--ui-primary)]' : '' }}">
                @svg('heroicon-o-home', 'w-5 h-5')
            </a>
            <a href="{{ route('academy.paths.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] {{ request()->routeIs('academy.paths.*') ? 'bg-[var(--ui-primary-5)] text-[var(--ui-primary)]' : '' }}">
                @svg('heroicon-o-rectangle-stack', 'w-5 h-5')
            </a>
            <a href="{{ route('academy.topics.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)] {{ request()->routeIs('academy.topics.*') ? 'bg-[var(--ui-primary-5)] text-[var(--ui-primary)]' : '' }}">
                @svg('heroicon-o-book-open', 'w-5 h-5')
            </a>
        </div>
    </div>
</div>
