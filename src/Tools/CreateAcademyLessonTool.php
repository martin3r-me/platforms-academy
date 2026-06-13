<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyTopic;
use Platform\Academy\Services\AcademyLessonService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class CreateAcademyLessonTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.lessons.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/lessons - Erstellt eine neue Lesson in einem Thema. ERFORDERLICH: topic_id, title. Content ist Markdown.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'topic_id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'summary' => ['type' => 'string', 'description' => 'Kurzbeschreibung (1-2 Saetze).'],
                'content' => ['type' => 'string', 'description' => 'Markdown-Content.'],
                'estimated_minutes' => ['type' => 'integer'],
                'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived'], 'description' => 'Default draft.'],
                'slug' => ['type' => 'string'],
                'sort_order' => ['type' => 'integer'],
            ],
            'required' => ['topic_id', 'title'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $topicId = (int) ($arguments['topic_id'] ?? 0);
            $topic = AcademyTopic::where('team_id', $resolved['team_id'])->find($topicId);
            if (!$topic) {
                return ToolResult::error('NOT_FOUND', 'Thema nicht gefunden.');
            }

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title ist erforderlich.');
            }

            $lesson = app(AcademyLessonService::class)->create(
                $topic,
                $context->user->id,
                array_merge($arguments, ['title' => $title]),
            );

            return ToolResult::success([
                'id' => $lesson->id,
                'uuid' => $lesson->uuid,
                'topic_id' => $lesson->academy_topic_id,
                'slug' => $lesson->slug,
                'title' => $lesson->title,
                'status' => $lesson->status,
                'message' => "Lesson '{$lesson->title}' erstellt (Status: {$lesson->status}).",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'lessons', 'create'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
