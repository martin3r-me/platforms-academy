<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyTopic;
use Platform\Academy\Services\AcademyTopicService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class DeleteAcademyTopicTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.topics.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /academy/topics - Loescht ein Thema (cascadiert auf seine Lessons). ERFORDERLICH: topic_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'topic_id' => ['type' => 'integer'],
            ],
            'required' => ['topic_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $topicId = (int) ($arguments['topic_id'] ?? 0);
            $topic = AcademyTopic::where('team_id', $resolved['team_id'])->find($topicId);
            if (!$topic) {
                return ToolResult::error('NOT_FOUND', 'Thema nicht gefunden.');
            }

            $title = $topic->title;
            app(AcademyTopicService::class)->delete($topic);

            return ToolResult::success([
                'message' => "Thema '{$title}' geloescht (inkl. zugehoeriger Lessons).",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'topics', 'delete'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'destructive', 'idempotent' => true,
        ];
    }
}
