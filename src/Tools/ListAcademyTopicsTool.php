<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyTopicService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ListAcademyTopicsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.topics.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/topics - Listet alle Themen-Cluster des Teams.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer', 'description' => 'Optional: Team-ID.'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $topics = app(AcademyTopicService::class)->listForTeam($resolved['team_id']);

            return ToolResult::success([
                'team_id' => $resolved['team_id'],
                'count' => $topics->count(),
                'topics' => $topics->map(fn ($t) => [
                    'id' => $t->id,
                    'uuid' => $t->uuid,
                    'slug' => $t->slug,
                    'title' => $t->title,
                    'description' => $t->description,
                    'icon' => $t->icon,
                    'color' => $t->color,
                    'sort_order' => $t->sort_order,
                    'published_lessons_count' => $t->published_lessons_count,
                ])->all(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'topics', 'list'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
