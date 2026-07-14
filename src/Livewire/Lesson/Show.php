<?php

namespace Platform\Academy\Livewire\Lesson;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Services\AcademyEnrollmentService;
use Platform\Academy\Services\AcademyMarkdownService;
use Platform\Academy\Services\AcademyProgressService;
use Platform\Academy\Services\AcademyQuizService;

class Show extends Component
{
    public string $uuid;

    /** @var array<int, mixed> question_id => option_id (single) | [option_id,...] (multiple) */
    public array $quizAnswers = [];

    /** Ergebnis des letzten Auswertens (null = noch nicht abgegeben). */
    public ?array $quizResult = null;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function markComplete(): void
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);

        // Manueller Abschluss nur fuer Lektionen ohne Concept-Check.
        if ($lesson->quiz()->exists()) {
            return;
        }

        app(AcademyProgressService::class)->complete($user->id, $lesson);
    }

    public function reopen(): void
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);
        app(AcademyProgressService::class)->reopen($user->id, $lesson);
        $this->quizResult = null;
        $this->quizAnswers = [];
    }

    public function submitQuiz(): void
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);
        $quiz = $lesson->quiz()->with('questions.options')->first();

        if (!$quiz || $quiz->questions->isEmpty()) {
            return;
        }

        // Antworten normalisieren: alles zu Listen von Option-IDs.
        $answers = [];
        foreach ($quiz->questions as $question) {
            $raw = $this->quizAnswers[$question->id] ?? [];
            $ids = is_array($raw) ? $raw : [$raw];
            $answers[$question->id] = array_values(array_filter(array_map('intval', $ids)));
        }

        $outcome = app(AcademyQuizService::class)->submit($user->id, $quiz, $answers);
        $this->quizResult = $outcome['result'];
    }

    public function retryQuiz(): void
    {
        $this->quizAnswers = [];
        $this->quizResult = null;
    }

    public function startIfNeeded(): void
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);

        $existing = $lesson->progressFor($user->id);
        if (!$existing) {
            app(AcademyProgressService::class)->start($user->id, $lesson);
        }

        // Resume-Punkt fuer eingeschriebene Kurse mitziehen.
        app(AcademyEnrollmentService::class)->touch($user->id, $lesson);
    }

    public function render()
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);
        $this->startIfNeeded();

        $progress = $lesson->progressFor($user->id);
        $isCompleted = $progress && $progress->isCompleted();

        $markdown = app(AcademyMarkdownService::class);
        $renderedContent = $markdown->render($lesson->content);

        // Concept-Check dieser Lektion (optional). Existiert er, gatet er den Abschluss.
        $quiz = $lesson->quiz()->with('questions.options')->first();
        $quizQuestions = [];
        if ($quiz && $quiz->questions->isNotEmpty()) {
            foreach ($quiz->questions as $question) {
                $quizQuestions[] = [
                    'id' => $question->id,
                    'type' => $question->type,
                    'is_multiple' => $question->isMultiple(),
                    'prompt_html' => $markdown->render($question->prompt),
                    'explanation_html' => $question->explanation ? $markdown->render($question->explanation) : null,
                    'options' => $question->options->map(fn ($o) => [
                        'id' => $o->id,
                        'label' => $o->label,
                    ])->all(),
                ];
            }
        }
        $hasQuiz = $quizQuestions !== [];

        // Checkbox-Bindung braucht fuer Mehrfach-Fragen ein Array als Startwert — sonst
        // behandelt Livewire die Checkboxen als Boolean und hakt beim ersten Klick alle
        // Optionen derselben Frage an. Single-Fragen bekommen einen Skalar-Startwert.
        if ($hasQuiz && $this->quizResult === null) {
            foreach ($quiz->questions as $question) {
                if (!array_key_exists($question->id, $this->quizAnswers)) {
                    $this->quizAnswers[$question->id] = $question->isMultiple() ? [] : '';
                }
            }
        }

        $topicLessons = $lesson->topic->publishedLessons()->get(['id', 'uuid', 'title', 'sort_order']);

        $completedIdsInTopic = app(AcademyProgressService::class)
            ->completedLessonIdsForUser($user->id, $topicLessons->pluck('id')->all());
        $completedSet = array_flip($completedIdsInTopic);

        $pathMemberships = $lesson->paths()
            ->where('status', \Platform\Academy\Models\AcademyPath::STATUS_PUBLISHED)
            ->with('category')
            ->get();

        // Prev/Next folgen dem KURS (Path), damit die Navigation ueber Themen-/Kapitel-
        // grenzen hinweg funktioniert. Ohne Kurs-Kontext (reines Bibliotheks-Stoebern)
        // bleibt es themenintern.
        $primaryPath = $pathMemberships->first();
        $sequence = $primaryPath
            ? $primaryPath->lessons()
                ->where('academy_lessons.status', AcademyLesson::STATUS_PUBLISHED)
                ->with('topic:id,title')
                ->get(['academy_lessons.id', 'academy_lessons.uuid', 'academy_lessons.title', 'academy_lessons.academy_topic_id'])
            : null;

        // Fallback auf die themeninterne Reihenfolge, wenn kein Kurs existiert oder die
        // Lektion (unerwartet) nicht in der Kurs-Sequenz liegt.
        if (!$sequence || $sequence->search(fn ($l) => $l->id === $lesson->id) === false) {
            $sequence = $topicLessons;
        }

        $currentIndex = $sequence->search(fn ($l) => $l->id === $lesson->id);
        $prev = $currentIndex !== false && $currentIndex > 0 ? $sequence[$currentIndex - 1] : null;
        $next = $currentIndex !== false && $currentIndex < $sequence->count() - 1 ? $sequence[$currentIndex + 1] : null;

        // Wechselt die naechste Lektion das Thema, ist es ein neues Kapitel.
        $nextIsNewChapter = $next && isset($next->academy_topic_id) && $next->academy_topic_id !== $lesson->academy_topic_id;
        $nextChapterTitle = $nextIsNewChapter ? ($next->topic?->title) : null;

        // Akzentfarbe des Hero: erbt die Farbe des Kurses (sonst Thema-Farbe, sonst Indigo).
        $accentColor = $pathMemberships->isNotEmpty()
            ? $pathMemberships->first()->coverColor()
            : (($lesson->topic->color && str_starts_with($lesson->topic->color, '#')) ? $lesson->topic->color : '#4F46E5');

        $this->dispatch('comms', [
            'model' => AcademyLesson::class,
            'modelId' => $lesson->id,
            'subject' => 'Lesson: ' . $lesson->title,
            'description' => $lesson->summary,
            'url' => route('academy.lessons.show', ['uuid' => $lesson->uuid]),
            'source' => 'academy.lessons.show',
            'recipients' => [],
            'meta' => ['view_type' => 'show', 'resource' => 'lesson', 'topic_id' => $lesson->topic->id],
        ]);

        return view('academy::livewire.lesson.show', [
            'lesson' => $lesson,
            'renderedContent' => $renderedContent,
            'isCompleted' => $isCompleted,
            'prev' => $prev,
            'next' => $next,
            'nextIsNewChapter' => $nextIsNewChapter,
            'nextChapterTitle' => $nextChapterTitle,
            'primaryPath' => $primaryPath,
            'topicLessons' => $topicLessons,
            'completedSet' => $completedSet,
            'pathMemberships' => $pathMemberships,
            'accentColor' => $accentColor,
            'hasQuiz' => $hasQuiz,
            'quiz' => $quiz,
            'quizQuestions' => $quizQuestions,
            'quizResult' => $this->quizResult,
        ])->layout('platform::layouts.app');
    }

    protected function resolveLesson($user): AcademyLesson
    {
        return AcademyLesson::query()
            ->where('uuid', $this->uuid)
            ->where('team_id', $user->currentTeam->id)
            ->firstOrFail();
    }
}
