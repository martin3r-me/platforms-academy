<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyPathService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class UpdateAcademyPathTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.paths.PUT';
    }

    public function getDescription(): string
    {
        return 'PUT /academy/paths - Aktualisiert einen Lernpfad. ERFORDERLICH: path_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'target_audience' => ['type' => 'string'],
                'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']],
                'icon' => ['type' => 'string'],
                'color' => ['type' => 'string'],
                'sort_order' => ['type' => 'integer'],
            ],
            'required' => ['path_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $pathId = (int) ($arguments['path_id'] ?? 0);
            $path = AcademyPath::where('team_id', $resolved['team_id'])->find($pathId);
            if (!$path) {
                return ToolResult::error('NOT_FOUND', 'Lernpfad nicht gefunden.');
            }

            $path = app(AcademyPathService::class)->update($path, $arguments);

            return ToolResult::success([
                'id' => $path->id,
                'uuid' => $path->uuid,
                'title' => $path->title,
                'status' => $path->status,
                'message' => "Lernpfad aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'paths', 'update'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
