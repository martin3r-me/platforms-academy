<div class="h-full">
<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Design" icon="heroicon-o-swatch" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Academy', 'href' => route('academy.dashboard'), 'icon' => 'academic-cap'],
            ['label' => 'Design', 'href' => route('academy.design')],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="max-w-5xl mx-auto space-y-12">

            {{-- HEADER --}}
            <div>
                <div class="text-[11px] font-medium uppercase tracking-[0.16em] text-gray-400" style="font-family: var(--ui-font-mono);">Academy · Design-Referenz</div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 mt-1" style="font-family: var(--ui-font-mono);">Muster &amp; Sprache</h1>
                <p class="text-[15px] text-gray-500 dark:text-gray-400 mt-2 max-w-2xl">
                    Lebende Referenz mit den echten Tokens der ausgelieferten UI — kann nicht von der Oberfläche abweichen. Nach jedem größeren UI-Wurf hier das betroffene Muster mitziehen.
                </p>
            </div>

            @php
                $capClass = 'text-[11px] font-semibold uppercase tracking-wider text-gray-400 mb-3';
            @endphp

            {{-- ===== KURS-ZUWEISUNG: LERNENDE ===== --}}
            <section class="space-y-3">
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-flag', 'w-5 h-5 text-[var(--ui-primary)]')
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Für dich zu erledigen</h2>
                    <span class="text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);">· Dashboard</span>
                </div>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 max-w-2xl -mt-1">Zugewiesene Kurse, nach Deadline sortiert. Überfällig in Rot, offene Pflicht in Indigo. Status-Leiste oben, Fortschritt unten.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- overdue --}}
                    <div class="rounded-2xl border bg-[var(--ui-surface)] overflow-hidden flex flex-col shadow-sm border-red-500/40">
                        <div class="px-4 py-2 flex items-center justify-between text-[11px] font-semibold bg-red-500/10 text-red-600 dark:text-red-400" style="font-family: var(--ui-font-mono);">
                            <span class="inline-flex items-center gap-1">@svg('heroicon-o-flag', 'w-3.5 h-3.5') Pflicht</span>
                            <span>Überfällig · 10.07.2026</span>
                        </div>
                        <div class="p-4 flex flex-col gap-2.5 flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider" style="font-family: var(--ui-font-mono); color: #4F46E5;">DEV · Grundlagen</span>
                            <span class="font-semibold text-[15px] text-gray-900 dark:text-gray-100 leading-tight">Sicherheits-Grundlagen</span>
                            <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-1.5 mt-1"><div class="h-1.5 rounded-full bg-red-500" style="width: 20%"></div></div>
                            <div class="flex items-center justify-between text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);"><span>1 / 5 Lektionen</span><span>20%</span></div>
                            <span class="mt-1 flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-white text-[13px] font-semibold bg-red-500">Fortsetzen @svg('heroicon-s-arrow-right', 'w-4 h-4')</span>
                        </div>
                    </div>
                    {{-- mandatory --}}
                    <div class="rounded-2xl border bg-[var(--ui-surface)] overflow-hidden flex flex-col shadow-sm border-[var(--ui-primary)]/30">
                        <div class="px-4 py-2 flex items-center justify-between text-[11px] font-semibold bg-[var(--ui-primary-10)] text-[var(--ui-primary)]" style="font-family: var(--ui-font-mono);">
                            <span class="inline-flex items-center gap-1">@svg('heroicon-o-flag', 'w-3.5 h-3.5') Pflicht</span>
                            <span>Fällig 25.07.2026</span>
                        </div>
                        <div class="p-4 flex flex-col gap-2.5 flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider" style="font-family: var(--ui-font-mono); color: #4F46E5;">DEV · Fundamente</span>
                            <span class="font-semibold text-[15px] text-gray-900 dark:text-gray-100 leading-tight">Fundamente der Webentwicklung</span>
                            <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-1.5 mt-1"><div class="h-1.5 rounded-full bg-[var(--ui-primary)]" style="width: 45%"></div></div>
                            <div class="flex items-center justify-between text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);"><span>15 / 33 Lektionen</span><span>45%</span></div>
                            <span class="mt-1 flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-white text-[13px] font-semibold bg-[var(--ui-primary)]">Fortsetzen @svg('heroicon-s-arrow-right', 'w-4 h-4')</span>
                        </div>
                    </div>
                    {{-- recommended --}}
                    <div class="rounded-2xl border bg-[var(--ui-surface)] overflow-hidden flex flex-col shadow-sm border-[var(--ui-primary)]/30">
                        <div class="px-4 py-2 flex items-center justify-between text-[11px] font-semibold bg-[var(--ui-primary-10)] text-[var(--ui-primary)]" style="font-family: var(--ui-font-mono);">
                            <span class="inline-flex items-center gap-1">@svg('heroicon-o-flag', 'w-3.5 h-3.5') Empfohlen</span>
                            <span>Fällig 31.08.2026</span>
                        </div>
                        <div class="p-4 flex flex-col gap-2.5 flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider" style="font-family: var(--ui-font-mono); color: #0d9488;">TEC · Web</span>
                            <span class="font-semibold text-[15px] text-gray-900 dark:text-gray-100 leading-tight">Wie das Web funktioniert</span>
                            <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-1.5 mt-1"><div class="h-1.5 rounded-full bg-[var(--ui-primary)]" style="width: 0%"></div></div>
                            <div class="flex items-center justify-between text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);"><span>0 / 6 Lektionen</span><span>0%</span></div>
                            <span class="mt-1 flex items-center justify-center gap-2 w-full px-4 py-2 rounded-lg text-white text-[13px] font-semibold bg-[var(--ui-primary)]">Starten @svg('heroicon-s-arrow-right', 'w-4 h-4')</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== PFLICHT-BANNER ===== --}}
            <section class="space-y-3">
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-flag', 'w-5 h-5 text-[var(--ui-primary)]')
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pflicht-Banner</h2>
                    <span class="text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);">· Kurs-Seite, über dem Hero</span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 border-[var(--ui-primary)]/30 bg-[var(--ui-primary-5)]">
                        <span class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center bg-[var(--ui-primary-10)] text-[var(--ui-primary)]">@svg('heroicon-o-flag', 'w-5 h-5')</span>
                        <div class="min-w-0 text-sm">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">Dieser Kurs ist dir als Pflicht zugewiesen.</div>
                            <div class="text-[13px] text-gray-500 dark:text-gray-400">Bitte bis <span class="font-medium">25.07.2026</span> abschließen.</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 border-red-500/40 bg-red-500/[0.06]">
                        <span class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center bg-red-500/15 text-red-600 dark:text-red-400">@svg('heroicon-o-flag', 'w-5 h-5')</span>
                        <div class="min-w-0 text-sm">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">Dieser Kurs ist dir als Pflicht zugewiesen.</div>
                            <div class="text-[13px] text-red-600 dark:text-red-400">Überfällig seit 10.07.2026 — bitte bald abschließen.</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== ZUWEISER ===== --}}
            <section class="space-y-3">
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-flag', 'w-5 h-5 text-[var(--ui-primary)]')
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Kurse zuweisen</h2>
                    <span class="text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);">· /academy/assignments</span>
                </div>

                @php
                    $fieldClass = 'w-full rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] px-3 py-2 text-sm text-gray-900 dark:text-gray-100';
                    $labelClass = 'block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1';
                @endphp

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 space-y-4 shadow-sm">
                    <h3 class="font-semibold text-[15px] text-gray-900 dark:text-gray-100">Neue Zuweisung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="{{ $labelClass }}">Kurs</label><div class="{{ $fieldClass }}">DEV-101 · Fundamente der Webentwicklung</div></div>
                        <div><label class="{{ $labelClass }}">Zuweisen an</label><div class="{{ $fieldClass }}">Ganzes Team</div></div>
                        <div><label class="{{ $labelClass }}">Team</label><div class="{{ $fieldClass }}">Vertrieb</div></div>
                        <div><label class="{{ $labelClass }}">Fällig bis</label><div class="{{ $fieldClass }}">25.07.2026</div></div>
                    </div>
                    <div class="flex flex-wrap items-center gap-5 pt-1 text-sm text-gray-700 dark:text-gray-300">
                        <span class="inline-flex items-center gap-2">
                            <span class="w-4 h-4 rounded flex items-center justify-center bg-[var(--ui-primary)] text-white">@svg('heroicon-s-check', 'w-3 h-3')</span> Pflicht (statt Empfehlung)
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="w-4 h-4 rounded border border-[var(--ui-border)]"></span> Sub-Teams einschließen
                        </span>
                    </div>
                    <div><label class="{{ $labelClass }}">Notiz (optional)</label><div class="{{ $fieldClass }} text-gray-400">z. B. Grund oder Hinweis für die Lernenden</div></div>
                    <div class="flex justify-end">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[var(--ui-primary)] text-white text-sm font-semibold">@svg('heroicon-o-flag', 'w-4 h-4') Zuweisen</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] shadow-sm">
                    @php
                        $rules = [
                            ['title' => 'Fundamente der Webentwicklung', 'badge' => 'Pflicht', 'mand' => true, 'target' => 'Team Vertrieb', 'due' => '25.07.2026', 'done' => 6, 'total' => 9, 'over' => 2],
                            ['title' => 'Sicherheits-Grundlagen', 'badge' => 'Pflicht', 'mand' => true, 'target' => 'Anna Berger', 'due' => '10.07.2026', 'done' => 0, 'total' => 1, 'over' => 1],
                            ['title' => 'Wie das Web funktioniert', 'badge' => 'Empfehlung', 'mand' => false, 'target' => 'Team Empfang', 'due' => null, 'done' => 4, 'total' => 4, 'over' => 0],
                        ];
                    @endphp
                    @foreach($rules as $i => $rule)
                        @php($pct = $rule['total'] > 0 ? (int) round($rule['done'] / $rule['total'] * 100) : 0)
                        <div class="p-5 {{ $i < count($rules) - 1 ? 'border-b border-[var(--ui-border)]' : '' }}">
                            <div class="flex items-start justify-between gap-4 flex-wrap">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-[15px] text-gray-900 dark:text-gray-100">{{ $rule['title'] }}</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $rule['mand'] ? 'bg-[var(--ui-primary-10)] text-[var(--ui-primary)]' : 'bg-[var(--ui-muted-10)] text-gray-500 dark:text-gray-400' }}" style="font-family: var(--ui-font-mono);">{{ $rule['badge'] }}</span>
                                    </div>
                                    <div class="text-[13px] text-gray-500 dark:text-gray-400 mt-1">An: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $rule['target'] }}</span>@if($rule['due']) · fällig bis {{ $rule['due'] }}@endif</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs text-gray-500 border border-[var(--ui-border)]">@svg('heroicon-o-arrow-path', 'w-4 h-4') Sync</span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs text-gray-400">@svg('heroicon-o-x-mark', 'w-4 h-4') Widerrufen</span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="w-full bg-[var(--ui-muted-10)] rounded-full h-1.5"><div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $pct }}%"></div></div>
                                <div class="flex items-center gap-4 mt-1.5 text-[11px] text-gray-400" style="font-family: var(--ui-font-mono);">
                                    <span>{{ $rule['done'] }} / {{ $rule['total'] }} abgeschlossen ({{ $pct }}%)</span>
                                    @if($rule['over'] > 0)<span class="text-red-500">{{ $rule['over'] }} überfällig</span>@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ===== TOKENS ===== --}}
            <section class="space-y-3">
                <div class="flex items-center gap-2">
                    @svg('heroicon-o-swatch', 'w-5 h-5 text-[var(--ui-primary)]')
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tokens</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 shadow-sm">
                        <div class="{{ $capClass }}">Farbe</div>
                        <div class="flex gap-3 flex-wrap">
                            <div class="text-center"><div class="w-14 h-10 rounded-lg" style="background: var(--ui-primary);"></div><div class="text-[10px] text-gray-400 mt-1" style="font-family: var(--ui-font-mono);">primary</div></div>
                            <div class="text-center"><div class="w-14 h-10 rounded-lg bg-emerald-500"></div><div class="text-[10px] text-gray-400 mt-1" style="font-family: var(--ui-font-mono);">success</div></div>
                            <div class="text-center"><div class="w-14 h-10 rounded-lg bg-red-500"></div><div class="text-[10px] text-gray-400 mt-1" style="font-family: var(--ui-font-mono);">overdue</div></div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 shadow-sm">
                        <div class="{{ $capClass }}">Typografie</div>
                        <div class="text-sm text-gray-700 dark:text-gray-300" style="font-family: var(--ui-font-mono);">JetBrains Mono — Headings, Codes</div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 mt-1">Satoshi — Fließtext &amp; UI</div>
                    </div>
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-surface)] p-5 shadow-sm">
                        <div class="{{ $capClass }}">Zustände</div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--ui-primary-10)] text-[var(--ui-primary)]" style="font-family: var(--ui-font-mono);">Pflicht</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--ui-muted-10)] text-gray-500" style="font-family: var(--ui-font-mono);">Empfehlung</span>
                        </div>
                        <div class="text-[12px] text-gray-500 dark:text-gray-400 mt-2">Indigo = offen · Rot = überfällig · Emerald = Fortschritt</div>
                    </div>
                </div>
            </section>

        </div>
    </x-ui-page-container>
</x-ui-page>
</div>
