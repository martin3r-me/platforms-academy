<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyCategoryService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class ListAcademyCategoriesTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.categories.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/categories - Listet alle Kurs-Kategorien ("Schools") des Teams inkl. Kurs-Anzahl.';
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

            $categories = app(AcademyCategoryService::class)->listForTeam($resolved['team_id']);

            return ToolResult::success([
                'team_id' => $resolved['team_id'],
                'count' => $categories->count(),
                'categories' => $categories->map(fn ($c) => [
                    'id' => $c->id,
                    'uuid' => $c->uuid,
                    'slug' => $c->slug,
                    'title' => $c->title,
                    'color' => $c->color,
                    'code_prefix' => $c->code_prefix,
                    'icon' => $c->icon,
                    'sort_order' => $c->sort_order,
                    'paths_count' => $c->paths_count,
                ])->all(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'categories', 'list'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
