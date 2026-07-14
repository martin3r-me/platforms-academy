<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Services\AcademyQuizService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class UpsertAcademyQuizTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.quizzes.upsert.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/quizzes/upsert - Legt den Concept-Check (Quiz) einer Lektion an oder ersetzt ihn komplett. ERFORDERLICH: lesson_id, questions[]. Jede Frage: type (single|multiple), prompt (Markdown), optional explanation, options[] mit label + is_correct. Optional: title, pass_pct (Default 70), shuffle_questions. Ein Quiz pro Lektion; besteht der Lernende es, gilt die Lektion als abgeschlossen.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'lesson_id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'pass_pct' => ['type' => 'integer', 'description' => 'Bestehensgrenze in Prozent. Default 70.'],
                'shuffle_questions' => ['type' => 'boolean'],
                'questions' => [
                    'type' => 'array',
                    'description' => 'Liste der Fragen. Ersetzt bestehende Fragen komplett.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'type' => ['type' => 'string', 'enum' => ['single', 'multiple']],
                            'prompt' => ['type' => 'string', 'description' => 'Fragetext (Markdown).'],
                            'explanation' => ['type' => 'string', 'description' => 'Erklaerung, die nach dem Beantworten gezeigt wird.'],
                            'options' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'label' => ['type' => 'string'],
                                        'is_correct' => ['type' => 'boolean'],
                                    ],
                                    'required' => ['label'],
                                ],
                            ],
                        ],
                        'required' => ['prompt', 'options'],
                    ],
                ],
            ],
            'required' => ['lesson_id', 'questions'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $lessonId = (int) ($arguments['lesson_id'] ?? 0);
            $lesson = AcademyLesson::where('team_id', $resolved['team_id'])->find($lessonId);
            if (!$lesson) {
                return ToolResult::error('NOT_FOUND', 'Lesson nicht gefunden.');
            }

            $questions = $arguments['questions'] ?? [];
            if (!is_array($questions) || count($questions) === 0) {
                return ToolResult::error('VALIDATION_ERROR', 'Mindestens eine Frage (questions[]) ist erforderlich.');
            }

            // Validierung: jede Frage braucht mind. eine korrekte Option.
            foreach ($questions as $i => $q) {
                $options = $q['options'] ?? [];
                if (!is_array($options) || count($options) < 2) {
                    return ToolResult::error('VALIDATION_ERROR', "Frage " . ($i + 1) . " braucht mindestens zwei Optionen.");
                }
                $correct = array_filter($options, fn ($o) => (bool) ($o['is_correct'] ?? false));
                if (count($correct) < 1) {
                    return ToolResult::error('VALIDATION_ERROR', "Frage " . ($i + 1) . " braucht mindestens eine korrekte Option (is_correct: true).");
                }
            }

            $quiz = app(AcademyQuizService::class)->upsert($lesson, [
                'title' => $arguments['title'] ?? null,
                'pass_pct' => $arguments['pass_pct'] ?? null,
                'shuffle_questions' => $arguments['shuffle_questions'] ?? null,
                'questions' => $questions,
            ]);

            return ToolResult::success([
                'id' => $quiz->id,
                'uuid' => $quiz->uuid,
                'lesson_id' => $lesson->id,
                'pass_pct' => $quiz->passThreshold(),
                'questions_count' => $quiz->questions->count(),
                'message' => "Concept-Check fuer '{$lesson->title}' gespeichert ({$quiz->questions->count()} Fragen).",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'quizzes', 'upsert'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
