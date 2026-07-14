<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class DeleteAcademyQuizTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.quizzes.DELETE';
    }

    public function getDescription(): string
    {
        return 'DELETE /academy/quizzes - Entfernt den Concept-Check einer Lektion (inkl. Fragen, Optionen, Versuche). Danach ist die Lektion wieder ohne Quiz abschliessbar. Identifikation per lesson_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'lesson_id' => ['type' => 'integer'],
            ],
            'required' => ['lesson_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $lessonId = (int) ($arguments['lesson_id'] ?? 0);
            $lesson = AcademyLesson::where('team_id', $resolved['team_id'])->with('quiz')->find($lessonId);
            if (!$lesson) {
                return ToolResult::error('NOT_FOUND', 'Lesson nicht gefunden.');
            }
            if (!$lesson->quiz) {
                return ToolResult::error('NOT_FOUND', 'Diese Lektion hat keinen Concept-Check.');
            }

            $lesson->quiz->delete(); // cascade: Fragen, Optionen, Versuche

            return ToolResult::success([
                'lesson_id' => $lesson->id,
                'message' => "Concept-Check von '{$lesson->title}' entfernt.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'quizzes', 'delete'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true,
        ];
    }
}
