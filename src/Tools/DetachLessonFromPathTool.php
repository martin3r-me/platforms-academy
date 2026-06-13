<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyPathService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class DetachLessonFromPathTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.paths.lessons.detach.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/paths/lessons/detach - Entfernt eine Lesson aus einem Lernpfad (Lesson selbst bleibt erhalten).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
                'lesson_id' => ['type' => 'integer'],
            ],
            'required' => ['path_id', 'lesson_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $path = AcademyPath::where('team_id', $resolved['team_id'])->find((int) ($arguments['path_id'] ?? 0));
            if (!$path) return ToolResult::error('NOT_FOUND', 'Lernpfad nicht gefunden.');

            $lesson = AcademyLesson::where('team_id', $resolved['team_id'])->find((int) ($arguments['lesson_id'] ?? 0));
            if (!$lesson) return ToolResult::error('NOT_FOUND', 'Lesson nicht gefunden.');

            app(AcademyPathService::class)->detachLesson($path, $lesson);

            return ToolResult::success([
                'message' => "Lesson '{$lesson->title}' aus Pfad '{$path->title}' entfernt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'paths', 'detach'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true,
        ];
    }
}
