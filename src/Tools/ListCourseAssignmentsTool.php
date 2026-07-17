<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Models\AcademyUserAssignment;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Registry\AudienceResolverRegistry;

class ListCourseAssignmentsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.assignments.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/assignments - Listet die Kurs-Zuweisungen (Delegations-Regeln) eines Teams inkl. Compliance-Quote (zugewiesen / abgeschlossen / ueberfaellig). Optional filtern: path_id, status (active|archived).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
                'status' => ['type' => 'string', 'enum' => ['active', 'archived']],
            ],
            'required' => [],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $registry = app(AudienceResolverRegistry::class);

            $query = AcademyCourseAssignment::where('team_id', $resolved['team_id'])
                ->with('path:id,title,uuid')
                ->withCount([
                    'userAssignments as persons_total',
                    'userAssignments as persons_completed' => fn ($q) => $q->where('status', AcademyUserAssignment::STATUS_COMPLETED),
                    'userAssignments as persons_overdue' => fn ($q) => $q->where('status', AcademyUserAssignment::STATUS_OVERDUE),
                ])
                ->orderByDesc('id');

            if (!empty($arguments['path_id'])) {
                $query->where('academy_path_id', (int) $arguments['path_id']);
            }
            if (!empty($arguments['status'])) {
                $query->where('status', $arguments['status']);
            }

            $rows = $query->get()->map(fn (AcademyCourseAssignment $r) => [
                'id' => $r->id,
                'uuid' => $r->uuid,
                'path_id' => $r->academy_path_id,
                'path_title' => $r->path?->title,
                'target' => $registry->label($r->target_type, (int) $r->target_id, $r->team_id) ?? ($r->target_type . ' #' . $r->target_id),
                'target_type' => $r->target_type,
                'is_mandatory' => (bool) $r->is_mandatory,
                'starts_at' => $r->starts_at?->toDateString(),
                'due_at' => $r->due_at?->toDateString(),
                'status' => $r->status,
                'persons_total' => (int) $r->persons_total,
                'persons_completed' => (int) $r->persons_completed,
                'persons_overdue' => (int) $r->persons_overdue,
            ])->all();

            return ToolResult::success([
                'count' => count($rows),
                'assignments' => $rows,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'assignments', 'list'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
