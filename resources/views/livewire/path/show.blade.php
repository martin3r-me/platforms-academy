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

                @if($enrollment)
                    <button wire:click="drop"
                            wire:confirm="Kurs wirklich verlassen? Dein Lektions-Fortschritt bleibt erhalten."
                            class="w-full text-center text-xs text-gray-400 hover:text-red-500 transition">
                        Kurs verlassen
                    </button>
                @endif
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    @php
        $coverColor = $path->coverColor();
        $totalMin = $lessons->sum('estimated_minutes');
        $code = $path->code ?: strtoupper(\Illuminate\Support\Str::substr($path->slug, 0, 6));
        $prefix = $path->category?->code_prefix ?: (string) \Illuminate\Support\Str::of($code)->before('-');
    @endphp

    <x-ui-page-container>
        <div class="max-w-5xl mx-auto space-y-8">

            {{-- ===== PFLICHT-BANNER ===== --}}
            @if($assignment)
                @php
                    $aOverdue = $assignment->status === \Platform\Academy\Models\AcademyUserAssignment::STATUS_OVERDUE;
                    $aDue = $assignment->due_at;
                @endphp
                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $aOverdue ? 'border-red-500/40 bg-red-500/[0.06]' : 'border-[var(--ui-primary)]/30 bg-[var(--ui-primary-5)]' }}">
                    <span class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center {{ $aOverdue ? 'bg-red-500/15 text-red-600 dark:text-red-400' : 'bg-[var(--ui-primary-10)] text-[var(--ui-primary)]' }}">
                        @svg('heroicon-o-flag', 'w-5 h-5')
                    </span>
                    <div class="min-w-0 text-sm">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ $assignment->is_mandatory ? 'Dieser Kurs ist dir als Pflicht zugewiesen.' : 'Dieser Kurs wurde dir empfohlen.' }}
                        </div>
                        <div class="text-[13px] {{ $aOverdue ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                            @if($aOverdue)
                                Überfällig{{ $aDue ? ' seit '.$aDue->format('d.m.Y') : '' }} — bitte bald abschließen.
                            @elseif($aDue)
                                Bitte bis <span class="font-medium">{{ $aDue->format('d.m.Y') }}</span> abschließen.
                            @else
                                Kein festes Enddatum.
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- ===== HERO ===== --}}
            <div class="relative overflow-hidden rounded-3xl text-white shadow-lg"
                 style="background-image: linear-gradient(135deg, color-mix(in srgb, {{ $coverColor }} 92%, #ffffff), color-mix(in srgb, {{ $coverColor }} 62%, #000000));">
                <div class="pointer-events-none absolute inset-0" style="background-image: repeating-linear-gradient(115deg, rgba(255,255,255,.06) 0 1px, transparent 1px 26px);"></div>
                <div class="pointer-events-none absolute -right-6 -bottom-16 font-bold leading-none select-none" style="font-family: var(--ui-font-mono); font-size: 220px; color: rgba(255,255,255,.12); letter-spacing: -.05em;">{{ $prefix }}</div>

                <div class="relative z-10 p-8 md:p-11 flex flex-col gap-5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-bold tracking-wider px-2.5 py-1 rounded-full bg-white/15 backdrop-blur" style="font-family: var(--ui-font-mono);">{{ $code }}</span>
                        @if($path->category)
                            <span class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full bg-white/15 backdrop-blur">{{ $path->category->title }}</span>
                        @endif
                        @if($path->levelLabel())
                            <span class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full bg-white/90 text-gray-900" style="font-family: var(--ui-font-mono);">{{ $path->levelLabel() }}</span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-[2.6rem] leading-[1.08] font-bold tracking-tight max-w-3xl" style="font-family: var(--ui-font-mono); text-wrap: balance;">{{ $path->title }}</h1>

                    @if($path->description)
                        <p class="text-[15px] md:text-base leading-relaxed text-white/85 max-w-2xl">{{ $path->description }}</p>
                    @endif

                    <div class="flex items-center gap-4 text-sm text-white/80" style="font-family: var(--ui-font-mono);">
                        <span class="inline-flex items-center gap-1.5">@svg('heroicon-o-rectangle-stack', 'w-4 h-4') {{ $summary['total'] }} {{ $summary['total'] == 1 ? 'Lektion' : 'Lektionen' }}</span>
                        @if($totalMin)
                            <span class="inline-flex items-center gap-1.5">@svg('heroicon-o-clock', 'w-4 h-4') ~{{ $totalMin }} min</span>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="pt-1">
                        @if($enrollment)
                            @if($enrollment->isCompleted())
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/95 text-sm font-semibold" style="color: {{ $coverColor }};">
                                        @svg('heroicon-s-check-badge', 'w-5 h-5') Kurs abgeschlossen
                                    </div>
                                    @if($certificate)
                                        <a wire:navigate href="{{ route('academy.certificates.show', ['uuid' => $certificate->uuid]) }}"
                                           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white/15 backdrop-blur border border-white/30 text-sm font-bold text-white hover:bg-white/25 transition">
                                            @svg('heroicon-o-document-check', 'w-5 h-5') Zertifikat ansehen
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    @if($resumeLesson)
                                        <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $resumeLesson->uuid]) }}"
                                           class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white text-sm font-bold shadow-md hover:shadow-lg hover:scale-[1.02] transition" style="color: {{ $coverColor }};">
                                            Weiterlernen @svg('heroicon-s-arrow-right', 'w-4 h-4')
                                        </a>
                                    @endif
                                    <div class="flex-1 max-w-xs">
                                        <div class="flex items-center justify-between text-xs text-white/80 mb-1" style="font-family: var(--ui-font-mono);">
                                            <span>{{ $summary['completed'] }} / {{ $summary['total'] }}</span>
                                            <span>{{ $summary['pct'] }}%</span>
                                        </div>
                                        <div class="w-full bg-white/25 rounded-full h-2">
                                            <div class="h-2 rounded-full bg-white" style="width: {{ $summary['pct'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <button wire:click="enroll"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white text-sm font-bold shadow-md hover:shadow-lg hover:scale-[1.02] transition" style="color: {{ $coverColor }};">
                                @svg('heroicon-o-plus', 'w-5 h-5') In Kurs einschreiben
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== LEHRPLAN ===== --}}
            <div>
                <div class="flex items-baseline justify-between mb-4">
                    <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-gray-100" style="font-family: var(--ui-font-mono);">Lehrplan</h2>
                    <span class="text-xs text-gray-400" style="font-family: var(--ui-font-mono);">{{ $summary['completed'] }}/{{ $summary['total'] }} erledigt</span>
                </div>

                @if($lessons->isEmpty())
                    <div class="p-6 text-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                        Diesem Kurs sind noch keine Lektionen zugeordnet.
                    </div>
                @else
                    <ol class="space-y-2">
                        @foreach($lessons as $i => $lesson)
                            @php
                                $isDone = isset($completedSet[$lesson->id]);
                                $isCurrent = $resumeLesson && $resumeLesson->id === $lesson->id && !$isDone;
                            @endphp
                            <li>
                                <a wire:navigate href="{{ route('academy.lessons.show', ['uuid' => $lesson->uuid]) }}"
                                   class="group flex items-center gap-4 p-4 rounded-2xl border transition-all hover:shadow-md hover:-translate-y-0.5
                                          {{ $isCurrent ? 'border-[var(--ui-primary)]/40 bg-[var(--ui-primary-5)]' : 'border-[var(--ui-border)] bg-[var(--ui-surface)] hover:bg-[var(--ui-muted-5)]' }}">
                                    <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold
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
                                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--ui-primary-10)] text-[var(--ui-primary)]" style="font-family: var(--ui-font-mono);">Weiter hier</span>
                                            @endif
                                        </div>
                                        @if($lesson->summary)
                                            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed line-clamp-1">{{ $lesson->summary }}</p>
                                        @endif
                                    </div>
                                    @if($lesson->estimated_minutes)
                                        <div class="flex-shrink-0 text-[11px] text-gray-400 whitespace-nowrap" style="font-family: var(--ui-font-mono);">{{ $lesson->estimated_minutes }} min</div>
                                    @endif
                                    @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-300 group-hover:text-gray-400 flex-shrink-0 transition')
                                </a>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
            {{-- ===== VERWALTUNG · ZUWEISEN (nur Owner/Admin) ===== --}}
            @if($canManage)
                @php
                    $fc = 'w-full rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] px-3 py-2 text-sm text-gray-900 dark:text-gray-100';
                    $lc = 'block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1';
                @endphp
                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 shadow-sm space-y-5">
                    <div class="flex items-center gap-2">
                        @svg('heroicon-o-flag', 'w-5 h-5 text-[var(--ui-primary)]')
                        <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-gray-100" style="font-family: var(--ui-font-mono);">Kurs zuweisen</h2>
                        <span class="text-xs text-gray-400" style="font-family: var(--ui-font-mono);">· Verwaltung</span>
                    </div>

                    @if(session('academy_assign_ok'))
                        <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/[0.06] px-3 py-2 text-sm text-emerald-700 dark:text-emerald-300">{{ session('academy_assign_ok') }}</div>
                    @endif

                    <form wire:submit="assign" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $lc }}">Zuweisen an</label>
                            <select wire:model.live="assignTargetType" class="{{ $fc }}">
                                @foreach($manage['targetTypes'] as $tt)
                                    <option value="{{ $tt['type'] }}">{{ $tt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $lc }}">Ziel</label>
                            <select wire:model="assignTargetId" class="{{ $fc }}">
                                <option value="">— wählen —</option>
                                @foreach($manage['targetOptions'] as $opt)
                                    <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                            @error('assignTargetId') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="{{ $lc }}">Fällig bis <span class="normal-case text-gray-400">(optional)</span></label>
                            <input type="date" wire:model="assignDueAt" class="{{ $fc }}">
                        </div>
                        <div class="flex items-end gap-4 pb-1">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" wire:model="assignMandatory" class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]/40"> Pflicht
                            </label>
                            @if($assignTargetType === 'team')
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" wire:model="assignIncludeSubteams" class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]/40"> Sub-Teams
                                </label>
                            @endif
                        </div>
                        <div class="sm:col-span-2 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[var(--ui-primary)] text-white text-sm font-semibold hover:opacity-90 transition">
                                @svg('heroicon-o-flag', 'w-4 h-4') Zuweisen
                            </button>
                        </div>
                    </form>

                    @if($manage['rules']->isNotEmpty())
                        <div class="border-t border-[var(--ui-border)] pt-4 space-y-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400" style="font-family: var(--ui-font-mono);">Wer hat's zu tun</div>
                            @foreach($manage['rules'] as $a)
                                @php
                                    $total = (int) $a->persons_total; $done = (int) $a->persons_completed; $over = (int) $a->persons_overdue;
                                    $pct = $total > 0 ? (int) round($done / $total * 100) : 0;
                                    $arch = $a->status === \Platform\Academy\Models\AcademyCourseAssignment::STATUS_ARCHIVED;
                                @endphp
                                <div class="flex items-center gap-3 flex-wrap {{ $arch ? 'opacity-60' : '' }}">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">{{ $a->target_label }}</span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-full ml-1 {{ $a->is_mandatory ? 'bg-[var(--ui-primary-10)] text-[var(--ui-primary)]' : 'bg-[var(--ui-muted-10)] text-gray-500' }}" style="font-family: var(--ui-font-mono);">{{ $a->is_mandatory ? 'Pflicht' : 'Empf.' }}</span>
                                            @if($a->due_at)<span class="text-xs text-gray-400"> · bis {{ $a->due_at->format('d.m.Y') }}</span>@endif
                                            @if($arch)<span class="text-xs text-gray-400"> · widerrufen</span>@endif
                                        </div>
                                        <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-1.5 mt-1"><div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $pct }}%"></div></div>
                                        <div class="flex items-center gap-3 mt-1 text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);">
                                            <span>{{ $done }} / {{ $total }} abgeschlossen ({{ $pct }}%)</span>
                                            @if($over > 0)<span class="text-red-500">{{ $over }} überfällig</span>@endif
                                        </div>
                                    </div>
                                    @unless($arch)
                                        <div class="flex items-center gap-1">
                                            <button wire:click="resyncAssignment({{ $a->id }})" title="Mitglieder neu auflösen"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-[var(--ui-primary)] hover:bg-[var(--ui-muted-5)] transition">@svg('heroicon-o-arrow-path', 'w-4 h-4')</button>
                                            <button wire:click="revokeAssignment({{ $a->id }})" wire:confirm="Zuweisung widerrufen? Einschreibung und Fortschritt bleiben erhalten."
                                                    title="Widerrufen"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-500/5 transition">@svg('heroicon-o-x-mark', 'w-4 h-4')</button>
                                        </div>
                                    @endunless
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
