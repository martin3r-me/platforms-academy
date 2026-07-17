<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class DeleteCourseAssignmentTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.assignments.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /academy/assignments - Widerruft eine Kurs-Zuweisung (archiviert die Regel, setzt offene pro-Person-Zuweisungen auf "revoked"). Einschreibung und Lernfortschritt bleiben erhalten. ERFORDERLICH: assignment_id.';
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

            app(AcademyAssignmentService::class)->revoke($rule);

            return ToolResult::success([
                'id' => $rule->id,
                'status' => $rule->status,
                'message' => 'Zuweisung widerrufen. Einschreibung und Fortschritt bleiben erhalten.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'assignments', 'delete'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true,
        ];
    }
}
