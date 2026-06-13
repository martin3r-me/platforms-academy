<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class GetAcademyLessonTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.lesson.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/lesson - Liefert eine Lesson mit vollem Markdown-Content. Identifikation per lesson_id ODER uuid.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'lesson_id' => ['type' => 'integer'],
                'uuid' => ['type' => 'string'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $query = AcademyLesson::where('team_id', $resolved['team_id'])->with('topic');
            if (!empty($arguments['lesson_id'])) {
                $query->where('id', (int) $arguments['lesson_id']);
            } elseif (!empty($arguments['uuid'])) {
                $query->where('uuid', (string) $arguments['uuid']);
            } else {
                return ToolResult::error('VALIDATION_ERROR', 'lesson_id oder uuid ist erforderlich.');
            }

            $lesson = $query->first();
            if (!$lesson) {
                return ToolResult::error('NOT_FOUND', 'Lesson nicht gefunden.');
            }

            return ToolResult::success([
                'id' => $lesson->id,
                'uuid' => $lesson->uuid,
                'topic' => [
                    'id' => $lesson->topic->id,
                    'uuid' => $lesson->topic->uuid,
                    'title' => $lesson->topic->title,
                ],
                'slug' => $lesson->slug,
                'title' => $lesson->title,
                'summary' => $lesson->summary,
                'content' => $lesson->content,
                'estimated_minutes' => $lesson->estimated_minutes,
                'status' => $lesson->status,
                'sort_order' => $lesson->sort_order,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'lessons', 'get'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
