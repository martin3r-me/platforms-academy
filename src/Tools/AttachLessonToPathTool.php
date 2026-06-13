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

class AttachLessonToPathTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.paths.lessons.attach.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/paths/lessons/attach - Fuegt eine Lesson einem Lernpfad hinzu (oder verschiebt sie, wenn schon zugeordnet). sort_order optional.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
                'lesson_id' => ['type' => 'integer'],
                'sort_order' => ['type' => 'integer'],
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

            $sortOrder = isset($arguments['sort_order']) ? (int) $arguments['sort_order'] : null;
            app(AcademyPathService::class)->attachLesson($path, $lesson, $sortOrder);

            return ToolResult::success([
                'path_id' => $path->id,
                'lesson_id' => $lesson->id,
                'message' => "Lesson '{$lesson->title}' an Pfad '{$path->title}' angehaengt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'paths', 'attach'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
