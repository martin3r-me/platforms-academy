<?php

namespace Platform\Academy\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyLessonProgress;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyPathEnrollment;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Academy\Services\AcademyCategoryService;
use Platform\Academy\Services\AcademyCertificateService;
use Platform\Academy\Services\AcademyEnrollmentService;

class Dashboard extends Component
{
    public function rendered(): void
    {
        $this->dispatch('comms', [
            'model' => null,
            'modelId' => null,
            'subject' => 'Academy Dashboard',
            'description' => 'Übersicht aller Kurse, Kategorien und Lernfortschritt',
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

        // Zugewiesene / Pflichtkurse (offen), nach Deadline sortiert.
        $assignments = app(AcademyAssignmentService::class)->openForUser($user->id, $teamId)
            ->map(fn ($ua) => [
                'ua' => $ua,
                'path' => $ua->path,
                'progress' => $ua->path?->progressFor($user->id),
            ])
            ->filter(fn ($r) => $r['path'] !== null)
            ->values();

        // "Meine Academy" — eingeschriebene Kurse mit Fortschritt + Resume
        $enrollmentRows = app(AcademyEnrollmentService::class)->activeForUser($user->id, $teamId);
        $activeCourses = $enrollmentRows->filter(fn ($r) => !$r['enrollment']->isCompleted())->take(6);

        // Abgeschlossene Kurse + zugehoerige Zertifikate.
        $certService = app(AcademyCertificateService::class);
        $completedCourses = $enrollmentRows
            ->filter(fn ($r) => $r['enrollment']->isCompleted())
            ->map(function ($r) use ($certService, $user) {
                $r['certificate'] = $certService->forUserPath($user->id, $r['path']);
                return $r;
            })
            ->values();

        $enrolledPathIds = $enrollmentRows->map(fn ($r) => $r['path']->id)->all();

        // Kategorien für den Katalog-Filter
        $categories = app(AcademyCategoryService::class)->listForTeam($teamId);

        // "Kurse entdecken" — veröffentlichte Kurse, in die man noch nicht eingeschrieben ist
        $discover = AcademyPath::query()
            ->where('team_id', $teamId)
            ->where('status', AcademyPath::STATUS_PUBLISHED)
            ->when($enrolledPathIds, fn ($q) => $q->whereNotIn('id', $enrolledPathIds))
            ->with('category')
            ->withCount(['publishedLessons as lessons_count'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get();

        // Kennzahlen
        $lessonsCount = AcademyLesson::where('team_id', $teamId)->where('status', AcademyLesson::STATUS_PUBLISHED)->count();
        $completedCount = AcademyLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('status', AcademyLessonProgress::STATUS_COMPLETED)
            ->count();
        $completedThisWeek = AcademyLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('status', AcademyLessonProgress::STATUS_COMPLETED)
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();
        $enrolledCount = AcademyPathEnrollment::where('user_id', $user->id)->where('team_id', $teamId)->count();

        return view('academy::livewire.dashboard', [
            'firstName' => str($user->name)->explode(' ')->first(),
            'assignments' => $assignments,
            'activeCourses' => $activeCourses,
            'completedCourses' => $completedCourses,
            'categories' => $categories,
            'discover' => $discover,
            'lessonsCount' => $lessonsCount,
            'completedCount' => $completedCount,
            'completedThisWeek' => $completedThisWeek,
            'enrolledCount' => $enrolledCount,
        ])->layout('platform::layouts.app');
    }
}
