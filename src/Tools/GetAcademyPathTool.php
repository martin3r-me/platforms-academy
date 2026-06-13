<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class GetAcademyPathTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.path.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/path - Liefert einen Lernpfad inkl. zugeordneter Lessons in Reihenfolge.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
                'uuid' => ['type' => 'string'],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $query = AcademyPath::where('team_id', $resolved['team_id']);
            if (!empty($arguments['path_id'])) {
                $query->where('id', (int) $arguments['path_id']);
            } elseif (!empty($arguments['uuid'])) {
                $query->where('uuid', (string) $arguments['uuid']);
            } else {
                return ToolResult::error('VALIDATION_ERROR', 'path_id oder uuid ist erforderlich.');
            }

            $path = $query->first();
            if (!$path) {
                return ToolResult::error('NOT_FOUND', 'Lernpfad nicht gefunden.');
            }

            $lessons = $path->lessons()->with('topic:id,uuid,title')->get();

            return ToolResult::success([
                'id' => $path->id,
                'uuid' => $path->uuid,
                'slug' => $path->slug,
                'title' => $path->title,
                'description' => $path->description,
                'target_audience' => $path->target_audience,
                'status' => $path->status,
                'icon' => $path->icon,
                'color' => $path->color,
                'sort_order' => $path->sort_order,
                'lessons' => $lessons->map(fn ($l) => [
                    'id' => $l->id,
                    'uuid' => $l->uuid,
                    'title' => $l->title,
                    'summary' => $l->summary,
                    'estimated_minutes' => $l->estimated_minutes,
                    'status' => $l->status,
                    'topic' => [
                        'id' => $l->topic?->id,
                        'title' => $l->topic?->title,
                    ],
                    'sort_order_in_path' => $l->pivot->sort_order,
                ])->all(),
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'paths', 'get'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
