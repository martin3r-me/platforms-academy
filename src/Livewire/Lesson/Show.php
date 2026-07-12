<?php

namespace Platform\Academy\Livewire\Lesson;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyLessonProgress;
use Platform\Academy\Services\AcademyEnrollmentService;
use Platform\Academy\Services\AcademyMarkdownService;
use Platform\Academy\Services\AcademyProgressService;

class Show extends Component
{
    public string $uuid;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function markComplete(): void
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);
        app(AcademyProgressService::class)->complete($user->id, $lesson);
    }

    public function reopen(): void
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);
        app(AcademyProgressService::class)->reopen($user->id, $lesson);
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

        $renderedContent = app(AcademyMarkdownService::class)->render($lesson->content);

        $topicLessons = $lesson->topic->publishedLessons()->get(['id', 'uuid', 'title', 'sort_order']);
        $currentIndex = $topicLessons->search(fn ($l) => $l->id === $lesson->id);
        $prev = $currentIndex !== false && $currentIndex > 0 ? $topicLessons[$currentIndex - 1] : null;
        $next = $currentIndex !== false && $currentIndex < $topicLessons->count() - 1 ? $topicLessons[$currentIndex + 1] : null;

        $completedIdsInTopic = app(AcademyProgressService::class)
            ->completedLessonIdsForUser($user->id, $topicLessons->pluck('id')->all());
        $completedSet = array_flip($completedIdsInTopic);

        $pathMemberships = $lesson->paths()
            ->where('status', \Platform\Academy\Models\AcademyPath::STATUS_PUBLISHED)
            ->with('category')
            ->get();

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
            'topicLessons' => $topicLessons,
            'completedSet' => $completedSet,
            'pathMemberships' => $pathMemberships,
            'accentColor' => $accentColor,
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
