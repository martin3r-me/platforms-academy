<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ResyncCourseAssignmentTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.assignments.resync.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/assignments/resync - Loest das Ziel einer Zuweisung erneut auf und legt fehlende pro-Person-Zuweisungen an (z.B. neue Team-/Org-Mitglieder). Idempotent. ERFORDERLICH: assignment_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'assignment_id' => ['type' => 'integer'],
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
            if (!$rule->isActive()) {
                return ToolResult::error('VALIDATION_ERROR', 'Zuweisung ist nicht aktiv.');
            }

            $added = app(AcademyAssignmentService::class)->fanOut($rule);

            return ToolResult::success([
                'id' => $rule->id,
                'added_persons' => $added,
                'message' => "Mitglieder neu aufgeloest. Neue Zuweisungen: {$added}.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'assignments', 'resync'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true,
        ];
    }
}
