<?php

namespace Platform\Academy\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyLessonProgress;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyTopic;

class Dashboard extends Component
{
    public function rendered(): void
    {
        $this->dispatch('comms', [
            'model' => null,
            'modelId' => null,
            'subject' => 'Academy Dashboard',
            'description' => 'Übersicht aller Lernpfade und Themen',
            'url' => route('academy.dashboard'),
            'source' => 'academy.dashboard',
            'recipients' => [],
            'meta' => ['view_type' => 'dashboard'],
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $teamId = $user?->currentTeam?->id;

        $paths = AcademyPath::query()
            ->where('team_id', $teamId)
            ->where('status', AcademyPath::STATUS_PUBLISHED)
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (AcademyPath $path) use ($user) {
                $path->setAttribute('progress_pct', $path->progressFor($user->id)['pct']);
                return $path;
            });

        $topicsCount = AcademyTopic::where('team_id', $teamId)->count();
        $lessonsCount = AcademyLesson::where('team_id', $teamId)->where('status', AcademyLesson::STATUS_PUBLISHED)->count();
        $completedCount = AcademyLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('status', AcademyLessonProgress::STATUS_COMPLETED)
            ->count();

        $continueLessons = AcademyLesson::query()
            ->where('team_id', $teamId)
            ->whereHas('progress', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('status', AcademyLessonProgress::STATUS_IN_PROGRESS))
            ->with('topic')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('academy::livewire.dashboard', [
            'paths' => $paths,
            'topicsCount' => $topicsCount,
            'lessonsCount' => $lessonsCount,
            'completedCount' => $completedCount,
            'continueLessons' => $continueLessons,
        ])->layout('platform::layouts.app');
    }
}
