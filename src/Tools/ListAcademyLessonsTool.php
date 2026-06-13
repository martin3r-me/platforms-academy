<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ListAcademyLessonsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.lessons.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/lessons - Listet Lessons. Optional gefiltert nach topic_id, status.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'topic_id' => ['type' => 'integer', 'description' => 'Nur Lessons dieses Themas.'],
                'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']],
                'limit' => ['type' => 'integer', 'description' => 'Default 50, max 200.'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $query = AcademyLesson::where('team_id', $resolved['team_id'])
                ->with('topic:id,uuid,title')
                ->orderBy('academy_topic_id')
                ->orderBy('sort_order');

            if (!empty($arguments['topic_id'])) {
                $query->where('academy_topic_id', (int) $arguments['topic_id']);
            }
            if (!empty($arguments['status'])) {
                $query->where('status', (string) $arguments['status']);
            }

            $limit = min((int) ($arguments['limit'] ?? 50), 200);
            $lessons = $query->limit($limit)->get();

            return ToolResult::success([
                'team_id' => $resolved['team_id'],
                'count' => $lessons->count(),
                'lessons' => $lessons->map(fn ($l) => [
                    'id' => $l->id,
                    'uuid' => $l->uuid,
                    'topic_id' => $l->academy_topic_id,
                    'topic_title' => $l->topic?->title,
                    'slug' => $l->slug,
                    'title' => $l->title,
                    'summary' => $l->summary,
                    'estimated_minutes' => $l->estimated_minutes,
                    'status' => $l->status,
                    'sort_order' => $l->sort_order,
                ])->all(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'lessons', 'list'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
