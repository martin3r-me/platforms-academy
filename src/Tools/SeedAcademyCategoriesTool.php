<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyCategoryService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class SeedAcademyCategoriesTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.categories.seed.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/categories/seed - Legt die 6 Standard-Kategorien (AI, Frontend, Value Stream, Business, Sales, UX) fuer das Team an. Idempotent - bestehende bleiben unveraendert.';
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

            $categories = app(AcademyCategoryService::class)
                ->seedDefaults($resolved['team_id'], $context->user->id);

            return ToolResult::success([
                'team_id' => $resolved['team_id'],
                'count' => count($categories),
                'categories' => array_map(fn ($c) => [
                    'id' => $c->id,
                    'slug' => $c->slug,
                    'title' => $c->title,
                    'color' => $c->color,
                    'code_prefix' => $c->code_prefix,
                ], $categories),
                'message' => 'Standard-Kategorien angelegt (idempotent).',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'categories', 'seed'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true,
        ];
    }
}
