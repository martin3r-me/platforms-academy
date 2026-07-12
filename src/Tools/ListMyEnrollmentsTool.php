<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyEnrollmentService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ListMyEnrollmentsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.enrollments.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/enrollments - Listet die Kurse des aktuellen Users ("Meine Academy") inkl. Fortschritt und Resume-Lesson.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $rows = app(AcademyEnrollmentService::class)
                ->activeForUser($context->user->id, $resolved['team_id']);

            return ToolResult::success([
                'team_id' => $resolved['team_id'],
                'count' => $rows->count(),
                'enrollments' => $rows->map(fn ($row) => [
                    'path_id' => $row['path']->id,
                    'path_uuid' => $row['path']->uuid,
                    'code' => $row['path']->code,
                    'title' => $row['path']->title,
                    'category' => $row['path']->category?->title,
                    'status' => $row['enrollment']->status,
                    'progress_pct' => $row['progress']['pct'],
                    'completed' => $row['progress']['completed'],
                    'total' => $row['progress']['total'],
                    'resume_lesson' => $row['resume'] ? [
                        'uuid' => $row['resume']->uuid,
                        'title' => $row['resume']->title,
                    ] : null,
                ])->all(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'enrollments', 'list'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
