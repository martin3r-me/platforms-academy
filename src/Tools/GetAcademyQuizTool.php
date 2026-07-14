<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class GetAcademyQuizTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.quiz.GET';
    }

    public function getDescription(): string
    {
        return 'GET /academy/quiz - Liefert den Concept-Check (Quiz) einer Lektion inkl. Fragen, Optionen und Loesungsschluessel. Identifikation per lesson_id.';
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
            $lesson = AcademyLesson::where('team_id', $resolved['team_id'])
                ->with('quiz.questions.options')
                ->find($lessonId);
            if (!$lesson) {
                return ToolResult::error('NOT_FOUND', 'Lesson nicht gefunden.');
            }

            $quiz = $lesson->quiz;
            if (!$quiz) {
                return ToolResult::success([
                    'lesson_id' => $lesson->id,
                    'quiz' => null,
                    'message' => 'Diese Lektion hat noch keinen Concept-Check.',
                ]);
            }

            return ToolResult::success([
                'lesson_id' => $lesson->id,
                'quiz' => [
                    'id' => $quiz->id,
                    'uuid' => $quiz->uuid,
                    'title' => $quiz->title,
                    'pass_pct' => $quiz->passThreshold(),
                    'shuffle_questions' => $quiz->shuffle_questions,
                    'questions' => $quiz->questions->map(fn ($q) => [
                        'id' => $q->id,
                        'type' => $q->type,
                        'prompt' => $q->prompt,
                        'explanation' => $q->explanation,
                        'options' => $q->options->map(fn ($o) => [
                            'id' => $o->id,
                            'label' => $o->label,
                            'is_correct' => $o->is_correct,
                        ])->values(),
                    ])->values(),
                ],
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'query', 'tags' => ['academy', 'quizzes', 'get'],
            'read_only' => true, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'read', 'idempotent' => true,
        ];
    }
}
