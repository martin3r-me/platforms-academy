<?php

namespace Platform\Academy\Livewire\Path;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyUserAssignment;
use Platform\Academy\Services\AcademyCertificateService;
use Platform\Academy\Services\AcademyEnrollmentService;
use Platform\Academy\Services\AcademyProgressService;

class Show extends Component
{
    public string $uuid;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function enroll(): void
    {
        $user = Auth::user();
        $path = $this->resolvePath($user);
        app(AcademyEnrollmentService::class)->enroll($user->id, $path);
    }

    public function drop(): void
    {
        $user = Auth::user();
        $path = $this->resolvePath($user);
        app(AcademyEnrollmentService::class)->drop($user->id, $path);
    }

    public function render()
    {
        $user = Auth::user();
        $path = $this->resolvePath($user);

        $lessons = $path->publishedLessons()->with('topic')->get();
        $summary = app(AcademyProgressService::class)->summaryForPath($user->id, $path);

        $completedIds = app(AcademyProgressService::class)
            ->completedLessonIdsForUser($user->id, $lessons->pluck('id')->all());
        $completedSet = array_flip($completedIds);

        $enrollment = $path->enrollmentFor($user->id);
        $resumeLesson = $enrollment
            ? app(AcademyEnrollmentService::class)->resumeLesson($enrollment)
            : null;

        $certificate = app(AcademyCertificateService::class)->forUserPath($user->id, $path);

        // Offene Zuweisung dieses Users für diesen Kurs (für den Pflicht-Banner).
        // Das Zuweisen selbst passiert per MCP (academy.assignments.*), nicht in der UI.
        $assignment = AcademyUserAssignment::where('user_id', $user->id)
            ->where('academy_path_id', $path->id)
            ->whereIn('status', [
                AcademyUserAssignment::STATUS_ASSIGNED,
                AcademyUserAssignment::STATUS_IN_PROGRESS,
                AcademyUserAssignment::STATUS_OVERDUE,
            ])
            ->orderByRaw('due_at is null, due_at asc')
            ->first();

        $this->dispatch('comms', [
            'model' => \Platform\Academy\Models\AcademyPath::class,
            'modelId' => $path->id,
            'subject' => 'Academy: ' . $path->title,
            'description' => $path->description,
            'url' => route('academy.paths.show', ['uuid' => $path->uuid]),
            'source' => 'academy.paths.show',
            'recipients' => [],
            'meta' => ['view_type' => 'show', 'resource' => 'path'],
        ]);

        return view('academy::livewire.path.show', [
            'path' => $path,
            'lessons' => $lessons,
            'summary' => $summary,
            'completedSet' => $completedSet,
            'enrollment' => $enrollment,
            'resumeLesson' => $resumeLesson,
            'certificate' => $certificate,
            'assignment' => $assignment,
        ])->layout('platform::layouts.app');
    }

    protected function resolvePath($user): AcademyPath
    {
        return AcademyPath::query()
            ->with('category')
            ->where('uuid', $this->uuid)
            ->where('team_id', $user->currentTeam->id)
            ->firstOrFail();
    }
}
