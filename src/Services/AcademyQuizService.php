<?php

namespace Platform\Academy\Services;

use Illuminate\Support\Facades\DB;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyQuiz;
use Platform\Academy\Models\AcademyQuizAttempt;
use Platform\Academy\Models\AcademyQuizQuestion;

class AcademyQuizService
{
    /**
     * Legt den Concept-Check einer Lektion an oder ersetzt ihn vollstaendig.
     * Fragen und Optionen werden bei jedem Upsert neu geschrieben (idempotent
     * gegen das uebergebene JSON), damit das Autoren-Tool die Quelle der Wahrheit ist.
     *
     * @param  array{title?: ?string, pass_pct?: ?int, shuffle_questions?: ?bool, questions: array<int, array>}  $payload
     */
    public function upsert(AcademyLesson $lesson, array $payload): AcademyQuiz
    {
        return DB::transaction(function () use ($lesson, $payload) {
            $quiz = AcademyQuiz::firstOrNew(['academy_lesson_id' => $lesson->id]);
            $quiz->team_id = $lesson->team_id;
            $quiz->academy_lesson_id = $lesson->id;
            $quiz->title = $payload['title'] ?? $quiz->title;
            $quiz->pass_pct = (int) ($payload['pass_pct'] ?? $quiz->pass_pct ?? AcademyQuiz::DEFAULT_PASS_PCT);
            $quiz->shuffle_questions = (bool) ($payload['shuffle_questions'] ?? $quiz->shuffle_questions ?? true);
            $quiz->save();

            // Fragen + Optionen komplett neu schreiben.
            $quiz->questions()->delete();

            $qSort = 0;
            foreach (($payload['questions'] ?? []) as $q) {
                $qSort += 10;
                $type = ($q['type'] ?? AcademyQuizQuestion::TYPE_SINGLE) === AcademyQuizQuestion::TYPE_MULTIPLE
                    ? AcademyQuizQuestion::TYPE_MULTIPLE
                    : AcademyQuizQuestion::TYPE_SINGLE;

                $question = $quiz->questions()->create([
                    'type' => $type,
                    'prompt' => (string) ($q['prompt'] ?? ''),
                    'explanation' => $q['explanation'] ?? null,
                    'sort_order' => $q['sort_order'] ?? $qSort,
                ]);

                $oSort = 0;
                foreach (($q['options'] ?? []) as $o) {
                    $oSort += 10;
                    $question->options()->create([
                        'label' => (string) ($o['label'] ?? ''),
                        'is_correct' => (bool) ($o['is_correct'] ?? false),
                        'sort_order' => $o['sort_order'] ?? $oSort,
                    ]);
                }
            }

            return $quiz->fresh(['questions.options']);
        });
    }

    /**
     * Bewertet Antworten gegen den Loesungsschluessel. Eine Frage gilt nur als
     * richtig, wenn die gewaehlten Optionen exakt der Menge der korrekten entsprechen.
     *
     * @param  array<int, array<int, int>>  $answers  question_id => [option_id, ...]
     * @return array{total: int, correct: int, score_pct: int, passed: bool, per_question: array<int, array>}
     */
    public function grade(AcademyQuiz $quiz, array $answers): array
    {
        $quiz->loadMissing('questions.options');
        $questions = $quiz->questions;
        $total = $questions->count();

        $correctCount = 0;
        $perQuestion = [];

        foreach ($questions as $question) {
            $selected = array_map('intval', (array) ($answers[$question->id] ?? []));
            sort($selected);

            $correctIds = $question->correctOptionIds();
            sort($correctIds);

            $isCorrect = $selected === $correctIds && $correctIds !== [];
            if ($isCorrect) {
                $correctCount++;
            }

            $perQuestion[$question->id] = [
                'correct' => $isCorrect,
                'selected' => $selected,
                'correct_ids' => $correctIds,
                'explanation' => $question->explanation,
            ];
        }

        $scorePct = $total > 0 ? (int) round($correctCount / $total * 100) : 0;

        return [
            'total' => $total,
            'correct' => $correctCount,
            'score_pct' => $scorePct,
            'passed' => $scorePct >= $quiz->passThreshold(),
            'per_question' => $perQuestion,
        ];
    }

    /**
     * Bewertet, protokolliert den Versuch und schliesst — bei Bestehen —
     * die zugehoerige Lektion ab (Gating).
     *
     * @param  array<int, array<int, int>>  $answers
     * @return array{attempt: AcademyQuizAttempt, result: array}
     */
    public function submit(int $userId, AcademyQuiz $quiz, array $answers): array
    {
        $result = $this->grade($quiz, $answers);

        $attempt = AcademyQuizAttempt::create([
            'user_id' => $userId,
            'academy_quiz_id' => $quiz->id,
            'team_id' => $quiz->team_id,
            'score_pct' => $result['score_pct'],
            'passed' => $result['passed'],
            'answers' => $answers,
        ]);

        if ($result['passed']) {
            $lesson = $quiz->lesson;
            if ($lesson) {
                app(AcademyProgressService::class)->complete($userId, $lesson);
            }
        }

        return ['attempt' => $attempt, 'result' => $result];
    }
}
