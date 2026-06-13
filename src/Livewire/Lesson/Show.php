<?php

namespace Platform\Academy\Livewire\Lesson;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyLessonProgress;
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
    }

    public function render()
    {
        $user = Auth::user();
        $lesson = $this->resolveLesson($user);
        $this->startIfNeeded();

        $progress = $lesson->progressFor($user->id);
        $isCompleted = $progress && $progress->isCompleted();

        $renderedContent = $lesson->content
            ? (string) Str::of($lesson->content)->markdown()
            : '';

        $topicLessons = $lesson->topic->publishedLessons()->get(['id', 'uuid', 'title', 'sort_order']);
        $currentIndex = $topicLessons->search(fn ($l) => $l->id === $lesson->id);
        $prev = $currentIndex !== false && $currentIndex > 0 ? $topicLessons[$currentIndex - 1] : null;
        $next = $currentIndex !== false && $currentIndex < $topicLessons->count() - 1 ? $topicLessons[$currentIndex + 1] : null;

        return view('academy::livewire.lesson.show', [
            'lesson' => $lesson,
            'renderedContent' => $renderedContent,
            'isCompleted' => $isCompleted,
            'prev' => $prev,
            'next' => $next,
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
