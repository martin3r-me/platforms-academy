<div class="h-full">
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Zuweisungen" icon="heroicon-o-flag" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Zuweisungen', 'href' => route('academy.assignments.index')],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="max-w-4xl mx-auto space-y-8">

            {{-- HEADER --}}
            <div>
                <div class="text-[11px] font-medium uppercase tracking-[0.16em] text-gray-400" style="font-family: var(--ui-font-mono);">Verwaltung</div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 mt-1" style="font-family: var(--ui-font-mono);">Kurse zuweisen</h1>
                <p class="text-[15px] text-gray-500 dark:text-gray-400 mt-2 max-w-xl">
                    Weise Kurse einzelnen Personen oder ganzen Teams zu — als Pflicht oder Empfehlung, mit Fälligkeitsdatum. Die zugewiesenen Personen werden automatisch eingeschrieben.
                </p>
            </div>

            @if(session('academy_assignment_ok'))
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/[0.06] px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                    {{ session('academy_assignment_ok') }}
                </div>
            @endif

            {{-- CREATE FORM --}}
            <form wire:submit="create" class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 space-y-4 shadow-sm">
                <h2 class="font-semibold text-[15px] text-gray-900 dark:text-gray-100">Neue Zuweisung</h2>

                @php($fieldClass = 'w-full rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[var(--ui-primary)]/40')
                @php($labelClass = 'block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $labelClass }}">Kurs</label>
                        <select wire:model="pathId" class="{{ $fieldClass }}">
                            <option value="">— Kurs wählen —</option>
                            @foreach($paths as $p)
                                <option value="{{ $p->id }}">{{ $p->code ? $p->code.' · ' : '' }}{{ $p->title }}</option>
                            @endforeach
                        </select>
                        @error('pathId') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Zuweisen an</label>
                        <select wire:model.live="targetType" class="{{ $fieldClass }}">
                            <option value="team">Ganzes Team</option>
                            <option value="user">Einzelne Person</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">{{ $targetType === 'user' ? 'Person' : 'Team' }}</label>
                        @if($targetType === 'user')
                            <select wire:model="targetId" class="{{ $fieldClass }}">
                                <option value="">— Person wählen —</option>
                                @foreach($persons as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <select wire:model="targetId" class="{{ $fieldClass }}">
                                <option value="">— Team wählen —</option>
                                @foreach($teams as $t)
                                    <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                                @endforeach
                            </select>
                        @endif
                        @error('targetId') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Fällig bis <span class="normal-case text-gray-400">(optional)</span></label>
                        <input type="date" wire:model="dueAt" class="{{ $fieldClass }}">
                        @error('dueAt') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-5 pt-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <input type="checkbox" wire:model="isMandatory" class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]/40">
                        Pflicht (statt Empfehlung)
                    </label>
                    @if($targetType === 'team')
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" wire:model="includeSubteams" class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]/40">
                            Sub-Teams einschließen
                        </label>
                    @endif
                </div>

                <div>
                    <label class="{{ $labelClass }}">Notiz <span class="normal-case text-gray-400">(optional)</span></label>
                    <textarea wire:model="note" rows="2" class="{{ $fieldClass }}" placeholder="z. B. Grund oder Hinweis für die Lernenden"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[var(--ui-primary)] text-white text-sm font-semibold hover:opacity-90 transition disabled:opacity-50"
                            wire:loading.attr="disabled" wire:target="create">
                        <span wire:loading.remove wire:target="create">@svg('heroicon-o-flag', 'w-4 h-4')</span>
                        <span wire:loading wire:target="create">@svg('heroicon-o-arrow-path', 'w-4 h-4 animate-spin')</span>
                        Zuweisen
                    </button>
                </div>
            </form>

            {{-- LIST --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Bestehende Zuweisungen</h2>

                @forelse($assignments as $a)
                    @php
                        $archived = $a->status === \Platform\Academy\Models\AcademyCourseAssignment::STATUS_ARCHIVED;
                        $total = (int) $a->persons_total;
                        $done = (int) $a->persons_completed;
                        $over = (int) $a->persons_overdue;
                        $pct = $total > 0 ? (int) round($done / $total * 100) : 0;
                    @endphp
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 mb-3 shadow-sm {{ $archived ? 'opacity-60' : '' }}">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-[15px] text-gray-900 dark:text-gray-100">{{ $a->path?->title ?? 'Kurs' }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $a->is_mandatory ? 'bg-[var(--ui-primary-10)] text-[var(--ui-primary)]' : 'bg-[var(--ui-muted-10)] text-gray-500 dark:text-gray-400' }}" style="font-family: var(--ui-font-mono);">
                                        {{ $a->is_mandatory ? 'Pflicht' : 'Empfehlung' }}
                                    </span>
                                    @if($archived)
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--ui-muted-10)] text-gray-500" style="font-family: var(--ui-font-mono);">widerrufen</span>
                                    @endif
                                </div>
                                <div class="text-[13px] text-gray-500 dark:text-gray-400 mt-1">
                                    An: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $a->target_label }}</span>
                                    @if($a->due_at) · fällig bis {{ $a->due_at->format('d.m.Y') }} @endif
                                </div>
                            </div>
                            @unless($archived)
                                <div class="flex items-center gap-2">
                                    <button wire:click="resync({{ $a->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs text-gray-500 hover:text-[var(--ui-primary)] hover:bg-[var(--ui-muted-5)] transition">
                                        @svg('heroicon-o-arrow-path', 'w-4 h-4') Sync
                                    </button>
                                    <button wire:click="revoke({{ $a->id }})"
                                            wire:confirm="Zuweisung widerrufen? Einschreibung und Fortschritt bleiben erhalten."
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs text-gray-400 hover:text-red-500 hover:bg-red-500/5 transition">
                                        @svg('heroicon-o-x-mark', 'w-4 h-4') Widerrufen
                                    </button>
                                </div>
                            @endunless
                        </div>

                        <div class="mt-3">
                            <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="flex items-center gap-4 mt-1.5 text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);">
                                <span>{{ $done }} / {{ $total }} abgeschlossen ({{ $pct }}%)</span>
                                @if($over > 0)<span class="text-red-500">{{ $over }} überfällig</span>@endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-muted-5)] text-gray-500 dark:text-gray-400">
                        Noch keine Zuweisungen. Lege oben die erste an.
                    </div>
                @endforelse
            </div>

        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
