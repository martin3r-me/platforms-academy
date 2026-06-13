<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyPathService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ListAcademyPathsTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.paths.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/paths - Listet alle Lernpfade des Teams.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'published_only' => ['type' => 'boolean'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $paths = app(AcademyPathService::class)->listForTeam(
                $resolved['team_id'],
                publishedOnly: (bool) ($arguments['published_only'] ?? false),
            );

            return ToolResult::success([
                'team_id' => $resolved['team_id'],
                'count' => $paths->count(),
                'paths' => $paths->map(fn ($p) => [
                    'id' => $p->id,
                    'uuid' => $p->uuid,
                    'slug' => $p->slug,
                    'title' => $p->title,
                    'description' => $p->description,
                    'target_audience' => $p->target_audience,
                    'status' => $p->status,
                    'icon' => $p->icon,
                    'color' => $p->color,
                    'sort_order' => $p->sort_order,
                    'lessons_count' => $p->lessons_count,
                ])->all(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'paths', 'list'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
