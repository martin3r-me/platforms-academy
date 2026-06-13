<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyPathService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ReorderPathLessonsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.paths.lessons.reorder.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/paths/lessons/reorder - Setzt die Reihenfolge der Lessons in einem Pfad neu. lesson_ids ist die Liste der Lesson-IDs in gewuenschter Reihenfolge.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
                'lesson_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'integer'],
                    'description' => 'Lesson-IDs in gewuenschter Reihenfolge.',
                ],
            ],
            'required' => ['path_id', 'lesson_ids'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $path = AcademyPath::where('team_id', $resolved['team_id'])->find((int) ($arguments['path_id'] ?? 0));
            if (!$path) return ToolResult::error('NOT_FOUND', 'Lernpfad nicht gefunden.');

            $lessonIds = $arguments['lesson_ids'] ?? [];
            if (!is_array($lessonIds) || empty($lessonIds)) {
                return ToolResult::error('VALIDATION_ERROR', 'lesson_ids muss ein nicht-leeres Array sein.');
            }

            $lessonIds = array_map('intval', $lessonIds);
            app(AcademyPathService::class)->reorderLessons($path, $lessonIds);

            return ToolResult::success([
                'path_id' => $path->id,
                'lessons_count' => count($lessonIds),
                'message' => "Reihenfolge in Pfad '{$path->title}' aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'paths', 'reorder'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true,
        ];
    }
}
