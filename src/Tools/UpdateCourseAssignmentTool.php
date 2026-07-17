<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Models\AcademyUserAssignment;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class UpdateCourseAssignmentTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.assignments.PUT';
    }

    public function getDescription(): string
    {
        return 'PUT /academy/assignments - Aendert eine Kurs-Zuweisung. ERFORDERLICH: assignment_id. Optional: due_at (YYYY-MM-DD), is_mandatory, note, status (active|archived). Deadline/Pflicht werden auf offene pro-Person-Zuweisungen uebernommen. status=archived widerruft die Zuweisung (Enrollment/Fortschritt bleiben).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'assignment_id' => ['type' => 'integer'],
                'due_at' => ['type' => 'string', 'description' => 'Neue Deadline (YYYY-MM-DD) oder leer zum Entfernen.'],
                'is_mandatory' => ['type' => 'boolean'],
                'note' => ['type' => 'string'],
                'status' => ['type' => 'string', 'enum' => ['active', 'archived']],
            ],
            'required' => ['assignment_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $rule = AcademyCourseAssignment::where('team_id', $resolved['team_id'])
                ->find((int) ($arguments['assignment_id'] ?? 0));
            if (!$rule) {
                return ToolResult::error('NOT_FOUND', 'Zuweisung nicht gefunden.');
            }

            // Widerruf?
            if (($arguments['status'] ?? null) === AcademyCourseAssignment::STATUS_ARCHIVED) {
                app(AcademyAssignmentService::class)->revoke($rule);
                return ToolResult::success([
                    'id' => $rule->id,
                    'status' => $rule->status,
                    'message' => 'Zuweisung widerrufen. Bestehende Einschreibungen bleiben erhalten.',
                ]);
            }

            $dueChanged = array_key_exists('due_at', $arguments);
            $mandatoryChanged = array_key_exists('is_mandatory', $arguments);

            if ($dueChanged) {
                $rule->due_at = $arguments['due_at'] ?: null;
            }
            if ($mandatoryChanged) {
                $rule->is_mandatory = (bool) $arguments['is_mandatory'];
            }
            if (array_key_exists('note', $arguments)) {
                $rule->note = $arguments['note'];
            }
            if (($arguments['status'] ?? null) === AcademyCourseAssignment::STATUS_ACTIVE) {
                $rule->status = AcademyCourseAssignment::STATUS_ACTIVE;
            }
            $rule->save();

            // Auf offene (nicht abgeschlossene/widerrufene) pro-Person-Zuweisungen uebernehmen.
            if ($dueChanged || $mandatoryChanged) {
                $open = AcademyUserAssignment::where('academy_course_assignment_id', $rule->id)
                    ->whereNotIn('status', [AcademyUserAssignment::STATUS_COMPLETED, AcademyUserAssignment::STATUS_REVOKED])
                    ->get();
                foreach ($open as $ua) {
                    if ($dueChanged) {
                        $ua->due_at = $rule->due_at;
                        // Ueberfaelligkeit anhand neuer Deadline neu bestimmen.
                        if ($ua->status === AcademyUserAssignment::STATUS_OVERDUE
                            && (!$rule->due_at || $rule->due_at->gte(now()->startOfDay()))) {
                            $ua->status = AcademyUserAssignment::STATUS_ASSIGNED;
                        }
                    }
                    if ($mandatoryChanged) {
                        $ua->is_mandatory = $rule->is_mandatory;
                    }
                    $ua->save();
                }
            }

            return ToolResult::success([
                'id' => $rule->id,
                'due_at' => $rule->due_at?->toDateString(),
                'is_mandatory' => (bool) $rule->is_mandatory,
                'status' => $rule->status,
                'message' => 'Zuweisung aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'assignments', 'update'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
