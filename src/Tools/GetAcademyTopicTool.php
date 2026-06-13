<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyTopic;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class GetAcademyTopicTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.topic.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/topic - Liefert ein Thema inkl. Lessons. Identifikation per topic_id ODER uuid.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'topic_id' => ['type' => 'integer', 'description' => 'ID des Themas.'],
                'uuid' => ['type' => 'string', 'description' => 'UUID des Themas.'],
                'include_drafts' => ['type' => 'boolean', 'description' => 'Auch Draft-Lessons listen (default false).'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];
            $teamId = $resolved['team_id'];

            $query = AcademyTopic::where('team_id', $teamId);
            if (!empty($arguments['topic_id'])) {
                $query->where('id', (int) $arguments['topic_id']);
            } elseif (!empty($arguments['uuid'])) {
                $query->where('uuid', (string) $arguments['uuid']);
            } else {
                return ToolResult::error('VALIDATION_ERROR', 'topic_id oder uuid ist erforderlich.');
            }

            $topic = $query->first();
            if (!$topic) {
                return ToolResult::error('NOT_FOUND', 'Thema nicht gefunden.');
            }

            $lessonsQuery = $topic->lessons();
            if (!($arguments['include_drafts'] ?? false)) {
                $lessonsQuery->where('status', AcademyLesson::STATUS_PUBLISHED);
            }

            return ToolResult::success([
                'id' => $topic->id,
                'uuid' => $topic->uuid,
                'slug' => $topic->slug,
                'title' => $topic->title,
                'description' => $topic->description,
                'icon' => $topic->icon,
                'color' => $topic->color,
                'sort_order' => $topic->sort_order,
                'lessons' => $lessonsQuery->get()->map(fn ($l) => [
                    'id' => $l->id,
                    'uuid' => $l->uuid,
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
            'category' => 'query', 'tags' => ['academy', 'topics', 'get'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
