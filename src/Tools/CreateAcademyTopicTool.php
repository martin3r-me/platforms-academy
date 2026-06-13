<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyTopicService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class CreateAcademyTopicTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.topics.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/topics - Legt ein neues Themen-Cluster an (z.B. "Prompting Basics", "Agentic Workflows").';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'title' => ['type' => 'string', 'description' => 'Titel des Themas (ERFORDERLICH).'],
                'description' => ['type' => 'string', 'description' => 'Optional: Kurzbeschreibung.'],
                'slug' => ['type' => 'string', 'description' => 'Optional: Slug (sonst aus Titel generiert).'],
                'icon' => ['type' => 'string', 'description' => 'Optional: Heroicon-Name, z.B. "heroicon-o-academic-cap".'],
                'color' => ['type' => 'string', 'description' => 'Optional: Farb-Token, z.B. "emerald".'],
                'sort_order' => ['type' => 'integer', 'description' => 'Optional: Sortier-Reihenfolge.'],
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

            $topic = app(AcademyTopicService::class)->create(
                $resolved['team_id'],
                $context->user->id,
                array_merge($arguments, ['title' => $title]),
            );

            return ToolResult::success([
                'id' => $topic->id,
                'uuid' => $topic->uuid,
                'slug' => $topic->slug,
                'title' => $topic->title,
                'team_id' => $topic->team_id,
                'message' => "Thema '{$topic->title}' erstellt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'topics', 'create'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
