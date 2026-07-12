<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyCategory;
use Platform\Academy\Services\AcademyCategoryService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class UpdateAcademyCategoryTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.categories.PUT';
    }

    public function getDescription(): string
    {
        return 'PUT /academy/categories - Aktualisiert eine Kategorie. ERFORDERLICH: category_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'category_id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'color' => ['type' => 'string'],
                'code_prefix' => ['type' => 'string'],
                'icon' => ['type' => 'string'],
                'sort_order' => ['type' => 'integer'],
            ],
            'required' => ['category_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $categoryId = (int) ($arguments['category_id'] ?? 0);
            $category = AcademyCategory::where('team_id', $resolved['team_id'])->find($categoryId);
            if (!$category) {
                return ToolResult::error('NOT_FOUND', 'Kategorie nicht gefunden.');
            }

            $category = app(AcademyCategoryService::class)->update($category, $arguments);

            return ToolResult::success([
                'id' => $category->id,
                'uuid' => $category->uuid,
                'title' => $category->title,
                'message' => 'Kategorie aktualisiert.',
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'categories', 'update'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
