<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyPathService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class DeleteAcademyPathTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.paths.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /academy/paths - Loescht einen Lernpfad (Lessons bleiben erhalten, nur die Zuordnung wird entfernt).';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
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

            $title = $path->title;
            app(AcademyPathService::class)->delete($path);

            return ToolResult::success([
                'message' => "Lernpfad '{$title}' geloescht.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'paths', 'delete'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'destructive', 'idempotent' => true,
        ];
    }
}
