<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyCategoryService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class CreateAcademyCategoryTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.categories.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/categories - Legt eine Kurs-Kategorie an (z.B. "AI & Automation"). Farbe treibt Kurs-Cover + Chip, code_prefix die Kurs-Codes (z.B. "AI" -> AI-101).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'title' => ['type' => 'string', 'description' => 'Titel der Kategorie (ERFORDERLICH).'],
                'description' => ['type' => 'string'],
                'color' => ['type' => 'string', 'description' => 'Hex-Basisfarbe, z.B. "#7C3AED".'],
                'code_prefix' => ['type' => 'string', 'description' => 'Prefix fuer Kurs-Codes, z.B. "AI".'],
                'icon' => ['type' => 'string', 'description' => 'Heroicon-Name, z.B. "heroicon-o-cpu-chip".'],
                'slug' => ['type' => 'string'],
                'sort_order' => ['type' => 'integer'],
            ],
            'required' => ['title'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title ist erforderlich.');
            }

            $category = app(AcademyCategoryService::class)->create(
                $resolved['team_id'],
                $context->user->id,
                array_merge($arguments, ['title' => $title]),
            );

            return ToolResult::success([
                'id' => $category->id,
                'uuid' => $category->uuid,
                'slug' => $category->slug,
                'title' => $category->title,
                'color' => $category->color,
                'code_prefix' => $category->code_prefix,
                'message' => "Kategorie '{$category->title}' erstellt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'categories', 'create'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
