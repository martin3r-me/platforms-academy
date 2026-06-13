<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyLessonProgress;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyTopic;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class AcademyOverviewTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.overview.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/overview - Zeigt eine Uebersicht der Academy: Anzahl Themen, Lessons, Lernpfade + eigene Lernfortschritte.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => [
                    'type' => 'integer',
                    'description' => 'Optional: Team-ID. Default: aktuelles Team aus Kontext.',
                ],
            ],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) {
                return $resolved['error'];
            }
            $teamId = $resolved['team_id'];
            $userId = $context->user->id;

            $topicsCount = AcademyTopic::where('team_id', $teamId)->count();
            $lessonsTotal = AcademyLesson::where('team_id', $teamId)->count();
            $lessonsPublished = AcademyLesson::where('team_id', $teamId)
                ->where('status', AcademyLesson::STATUS_PUBLISHED)->count();
            $pathsTotal = AcademyPath::where('team_id', $teamId)->count();
            $pathsPublished = AcademyPath::where('team_id', $teamId)
                ->where('status', AcademyPath::STATUS_PUBLISHED)->count();

            $myLessonIds = AcademyLesson::where('team_id', $teamId)->pluck('id');
            $myCompleted = AcademyLessonProgress::where('user_id', $userId)
                ->whereIn('academy_lesson_id', $myLessonIds)
                ->where('status', AcademyLessonProgress::STATUS_COMPLETED)
                ->count();
            $myInProgress = AcademyLessonProgress::where('user_id', $userId)
                ->whereIn('academy_lesson_id', $myLessonIds)
                ->where('status', AcademyLessonProgress::STATUS_IN_PROGRESS)
                ->count();

            return ToolResult::success([
                'team_id' => $teamId,
                'topics_count' => $topicsCount,
                'lessons_total' => $lessonsTotal,
                'lessons_published' => $lessonsPublished,
                'paths_total' => $pathsTotal,
                'paths_published' => $pathsPublished,
                'my_progress' => [
                    'completed' => $myCompleted,
                    'in_progress' => $myInProgress,
                ],
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query',
            'tags' => ['academy', 'overview'],
            'read_only' => true,
            'requires_auth' => true,
            'requires_team' => true,
            'risk_level' => 'read',
            'idempotent' => true,
        ];
    }
}
