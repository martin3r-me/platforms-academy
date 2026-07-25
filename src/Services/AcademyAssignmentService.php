<?php

namespace Platform\Academy\Services;

use Illuminate\Support\Collection;
use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyUserAssignment;
use Platform\Core\Registry\AudienceResolverRegistry;
use Platform\Notifications\Models\NotificationsNotice;
use Illuminate\Support\Facades\Route;

/**
 * Kern der Kurs-Delegation / Pflichtkurse. Entkoppelt: die Ziel-Auflösung
 * ("wer steckt hinter target_type/target_id?") läuft über die
 * AudienceResolverRegistry im Core — kein Wissen über Organisation o.Ä.
 */
class AcademyAssignmentService
{
    public function __construct(
        private AcademyEnrollmentService $enrollments,
    ) {}

    /**
     * Legt eine Delegations-Regel an und fächert sie sofort auf Personen aus.
     *
     * @param  array<string,mixed>  $options  Ziel-Optionen (z.B. include_subteams)
     * @param  array<string,mixed>  $attrs    is_mandatory, starts_at, due_at, note
     */
    public function assign(
        AcademyPath $path,
        string $targetType,
        int $targetId,
        array $options = [],
        ?int $assignedByUserId = null,
        array $attrs = []
    ): AcademyCourseAssignment {
        $rule = AcademyCourseAssignment::create([
            'team_id' => $path->team_id,
            'academy_path_id' => $path->id,
            'assigned_by_user_id' => $assignedByUserId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_options' => $options ?: null,
            'is_mandatory' => $attrs['is_mandatory'] ?? true,
            'starts_at' => $attrs['starts_at'] ?? null,
            'due_at' => $attrs['due_at'] ?? null,
            'note' => $attrs['note'] ?? null,
            'status' => AcademyCourseAssignment::STATUS_ACTIVE,
        ]);

        $this->fanOut($rule);

        return $rule;
    }

    /**
     * Löst das Ziel in User auf und legt fehlende pro-Person-Zuweisungen an
     * (idempotent, Auto-Enroll inklusive). Gibt die Zahl neu erzeugter zurück.
     */
    public function fanOut(AcademyCourseAssignment $rule): int
    {
        $path = $rule->path;
        if (!$path || !$rule->isActive()) {
            return 0;
        }

        $userIds = app(AudienceResolverRegistry::class)->resolve(
            $rule->target_type,
            (int) $rule->target_id,
            $rule->target_options ?? [],
            $rule->team_id,
        );

        $created = 0;
        foreach ($userIds as $userId) {
            $exists = AcademyUserAssignment::where('academy_course_assignment_id', $rule->id)
                ->where('user_id', $userId)
                ->exists();
            if ($exists) {
                continue;
            }

            // Auto-Enroll (idempotent) — Fortschritt/Zertifikat laufen dadurch mit.
            $enrollment = $this->enrollments->enroll($userId, $path);
            $isDone = $enrollment->isCompleted();

            $ua = AcademyUserAssignment::create([
                'team_id' => $rule->team_id,
                'academy_course_assignment_id' => $rule->id,
                'user_id' => $userId,
                'academy_path_id' => $path->id,
                'academy_path_enrollment_id' => $enrollment->id,
                'is_mandatory' => $rule->is_mandatory,
                'starts_at' => $rule->starts_at,
                'due_at' => $rule->due_at,
                'status' => $isDone
                    ? AcademyUserAssignment::STATUS_COMPLETED
                    : AcademyUserAssignment::STATUS_ASSIGNED,
                'completed_at' => $isDone ? now() : null,
            ]);

            if (!$isDone) {
                $this->notify($ua, 'assigned');
            }
            $created++;
        }

        $rule->forceFill(['last_synced_at' => now()])->save();

        return $created;
    }

    /**
     * Wird bei Kurs-Abschluss/-Reaktivierung aufgerufen (Hook im EnrollmentService).
     * Hält die pro-Person-Zuweisungen synchron zum Fortschritt.
     */
    public function syncPathCompletion(int $userId, AcademyPath $path, bool $isCompleted): void
    {
        AcademyUserAssignment::where('user_id', $userId)
            ->where('academy_path_id', $path->id)
            ->get()
            ->each(function (AcademyUserAssignment $ua) use ($isCompleted) {
                if ($ua->status === AcademyUserAssignment::STATUS_REVOKED) {
                    return;
                }

                if ($isCompleted && !$ua->isCompleted()) {
                    $ua->status = AcademyUserAssignment::STATUS_COMPLETED;
                    $ua->completed_at = now();
                    $ua->save();
                } elseif (!$isCompleted && $ua->isCompleted()) {
                    $ua->status = $this->openStatusFor($ua);
                    $ua->completed_at = null;
                    $ua->save();
                }
            });
    }

    /** Neue Mitglieder von team-/org-basierten Regeln nachziehen. */
    public function resyncActiveRules(): int
    {
        $count = 0;

        AcademyCourseAssignment::where('status', AcademyCourseAssignment::STATUS_ACTIVE)
            ->whereIn('target_type', ['team', 'org_entity', 'org_role'])
            ->with('path')
            ->chunkById(100, function ($rules) use (&$count) {
                foreach ($rules as $rule) {
                    $count += $this->fanOut($rule);
                }
            });

        return $count;
    }

    /** Offene, überfällige Zuweisungen markieren. Gibt die Zahl zurück. */
    public function refreshOverdue(): int
    {
        return AcademyUserAssignment::whereNotNull('due_at')
            ->whereDate('due_at', '<', now()->startOfDay())
            ->whereIn('status', [
                AcademyUserAssignment::STATUS_ASSIGNED,
                AcademyUserAssignment::STATUS_IN_PROGRESS,
            ])
            ->update(['status' => AcademyUserAssignment::STATUS_OVERDUE]);
    }

    /** Regel widerrufen: archivieren + offene pro-Person-Zuweisungen auf 'revoked'. */
    public function revoke(AcademyCourseAssignment $rule): void
    {
        $rule->status = AcademyCourseAssignment::STATUS_ARCHIVED;
        $rule->save();

        AcademyUserAssignment::where('academy_course_assignment_id', $rule->id)
            ->whereIn('status', [
                AcademyUserAssignment::STATUS_ASSIGNED,
                AcademyUserAssignment::STATUS_IN_PROGRESS,
                AcademyUserAssignment::STATUS_OVERDUE,
            ])
            ->update(['status' => AcademyUserAssignment::STATUS_REVOKED]);
    }

    /** Offene Zuweisungen eines Users (für Lernenden-Ansicht / MCP). */
    public function openForUser(int $userId, int $teamId): Collection
    {
        return AcademyUserAssignment::where('user_id', $userId)
            ->where('team_id', $teamId)
            ->whereIn('status', [
                AcademyUserAssignment::STATUS_ASSIGNED,
                AcademyUserAssignment::STATUS_IN_PROGRESS,
                AcademyUserAssignment::STATUS_OVERDUE,
            ])
            ->with('path.category')
            ->orderByRaw('due_at is null, due_at asc')
            ->get();
    }

    /**
     * Pflichtkurse eines Users mit Status + Fortschritt — Kontrakt für die
     * persönliche Sicht (home). Überfällig zuerst, dann offen, dann erledigt.
     *
     * @return array<int, array{path_uuid:?string, title:string, status:string, is_completed:bool, is_overdue:bool, due_at:?string, progress_pct:int}>
     */
    public function mandatoryForUser(int $userId, int $teamId): array
    {
        $items = AcademyUserAssignment::query()
            ->where('user_id', $userId)
            ->where('team_id', $teamId)
            ->where('is_mandatory', true)
            ->where('status', '!=', AcademyUserAssignment::STATUS_REVOKED)
            ->with('path')
            ->get()
            ->map(function (AcademyUserAssignment $ua) use ($userId) {
                $path = $ua->path;
                $progress = $path ? $path->progressFor($userId) : ['pct' => 0];

                return [
                    'path_uuid'    => $path?->uuid,
                    'title'        => $path?->title ?? 'Kurs',
                    'url'          => ($path?->uuid && Route::has('academy.paths.show'))
                        ? route('academy.paths.show', ['uuid' => $path->uuid])
                        : null,
                    'status'       => $ua->status,
                    'is_completed' => $ua->isCompleted(),
                    'is_overdue'   => $ua->status === AcademyUserAssignment::STATUS_OVERDUE,
                    'due_at'       => $ua->due_at?->toDateString(),
                    'progress_pct' => (int) ($progress['pct'] ?? 0),
                ];
            })
            ->all();

        usort($items, function ($a, $b) {
            $pa = $a['is_completed'] ? 2 : ($a['is_overdue'] ? 0 : 1);
            $pb = $b['is_completed'] ? 2 : ($b['is_overdue'] ? 0 : 1);
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }
            return strcmp($a['due_at'] ?? '9999-99-99', $b['due_at'] ?? '9999-99-99');
        });

        return $items;
    }

    /** Sanfte Erinnerungen: bald fällig + überfällig (In-App). */
    public function sendReminders(): void
    {
        $today = now()->startOfDay();

        AcademyUserAssignment::whereNotNull('due_at')
            ->whereIn('status', [
                AcademyUserAssignment::STATUS_ASSIGNED,
                AcademyUserAssignment::STATUS_IN_PROGRESS,
            ])
            ->whereDate('due_at', '>=', $today)
            ->whereDate('due_at', '<=', $today->copy()->addDays(7))
            ->where(fn ($q) => $q->whereNull('reminded_stage')->orWhere('reminded_stage', '!=', 'due_soon'))
            ->with('path')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $ua) {
                    $this->notify($ua, 'due_soon');
                    $ua->forceFill(['reminded_stage' => 'due_soon', 'last_reminded_at' => now()])->save();
                }
            });

        AcademyUserAssignment::where('status', AcademyUserAssignment::STATUS_OVERDUE)
            ->where(fn ($q) => $q->where('reminded_stage', '!=', 'overdue')
                ->orWhereNull('reminded_stage')
                ->orWhere('last_reminded_at', '<=', now()->subDays(7)))
            ->with('path')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $ua) {
                    $this->notify($ua, 'overdue');
                    $ua->forceFill(['reminded_stage' => 'overdue', 'last_reminded_at' => now()])->save();
                }
            });
    }

    /** In-App-Benachrichtigung (kind = assigned | due_soon | overdue). */
    public function notify(AcademyUserAssignment $ua, string $kind): void
    {
        $path = $ua->path;
        $courseTitle = $path?->title ?? 'Kurs';
        $due = $ua->due_at ? $ua->due_at->format('d.m.Y') : null;

        $title = match ($kind) {
            'assigned' => ($ua->is_mandatory ? 'Neuer Pflichtkurs: ' : 'Neuer Kurs für dich: ') . $courseTitle,
            'due_soon' => 'Erinnerung: ' . $courseTitle . ' wird fällig',
            'overdue' => 'Überfällig: ' . $courseTitle,
            default => $courseTitle,
        };

        $message = $ua->is_mandatory
            ? 'Dieser Kurs ist für dich verpflichtend.' . ($due ? ' Fällig bis ' . $due . '.' : '')
            : 'Dieser Kurs wurde dir empfohlen.' . ($due ? ' Bis ' . $due . '.' : '');

        NotificationsNotice::create([
            'notice_type' => 'academy_assignment_' . $kind,
            'title' => $title,
            'message' => $message,
            'user_id' => $ua->user_id,
            'team_id' => $ua->team_id,
            'noticable_type' => AcademyUserAssignment::class,
            'noticable_id' => $ua->id,
            'metadata' => [
                'academy_path_id' => $ua->academy_path_id,
                'academy_path_uuid' => $path?->uuid,
                'due_at' => $ua->due_at?->toDateString(),
                'kind' => $kind,
            ],
        ]);
    }

    private function openStatusFor(AcademyUserAssignment $ua): string
    {
        if ($ua->due_at && $ua->due_at->lt(now()->startOfDay())) {
            return AcademyUserAssignment::STATUS_OVERDUE;
        }

        return AcademyUserAssignment::STATUS_ASSIGNED;
    }
}
