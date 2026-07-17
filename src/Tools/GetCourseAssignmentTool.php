<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Registry\AudienceResolverRegistry;

class GetCourseAssignmentTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.assignment.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/assignment - Detail einer Kurs-Zuweisung inkl. Status pro Person. ERFORDERLICH: assignment_id.';
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
                ->with(['path:id,title,uuid', 'userAssignments.user:id,name'])
                ->find((int) ($arguments['assignment_id'] ?? 0));

            if (!$rule) {
                return ToolResult::error('NOT_FOUND', 'Zuweisung nicht gefunden.');
            }

            $registry = app(AudienceResolverRegistry::class);

            $persons = $rule->userAssignments->map(fn ($ua) => [
                'user_id' => $ua->user_id,
                'name' => $ua->user?->name,
                'status' => $ua->status,
                'due_at' => $ua->due_at?->toDateString(),
                'completed_at' => $ua->completed_at?->toDateString(),
            ])->all();

            return ToolResult::success([
                'id' => $rule->id,
                'uuid' => $rule->uuid,
                'path_id' => $rule->academy_path_id,
                'path_title' => $rule->path?->title,
                'target' => $registry->label($rule->target_type, (int) $rule->target_id, $rule->team_id) ?? ($rule->target_type . ' #' . $rule->target_id),
                'target_type' => $rule->target_type,
                'target_id' => $rule->target_id,
                'is_mandatory' => (bool) $rule->is_mandatory,
                'starts_at' => $rule->starts_at?->toDateString(),
                'due_at' => $rule->due_at?->toDateString(),
                'note' => $rule->note,
                'status' => $rule->status,
                'persons' => $persons,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'assignments', 'detail'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
