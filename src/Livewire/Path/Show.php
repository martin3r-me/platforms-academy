<?php

namespace Platform\Academy\Livewire\Path;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyUserAssignment;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Academy\Services\AcademyCertificateService;
use Platform\Academy\Services\AcademyEnrollmentService;
use Platform\Academy\Services\AcademyProgressService;
use Platform\Core\Models\Team;
use Platform\Core\Registry\AudienceResolverRegistry;

class Show extends Component
{
    public string $uuid;

    // Verwaltung: Kurs zuweisen (nur Owner/Admin).
    public string $assignTargetType = 'team';
    public ?int $assignTargetId = null;
    public bool $assignMandatory = true;
    public ?string $assignDueAt = null;
    public bool $assignIncludeSubteams = false;

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

    public function assign(): void
    {
        $user = Auth::user();
        $path = $this->resolvePath($user);
        if (!$this->userCanManage($user, $path->team_id)) {
            return;
        }

        $this->validate([
            'assignTargetType' => 'required|in:user,team',
            'assignTargetId' => 'required|integer',
            'assignDueAt' => 'nullable|date',
        ]);

        app(AcademyAssignmentService::class)->assign(
            $path,
            $this->assignTargetType,
            (int) $this->assignTargetId,
            ($this->assignTargetType === 'team' && $this->assignIncludeSubteams) ? ['include_subteams' => true] : [],
            $user->id,
            ['is_mandatory' => $this->assignMandatory, 'due_at' => $this->assignDueAt ?: null],
        );

        $this->reset(['assignTargetId', 'assignDueAt', 'assignIncludeSubteams']);
        $this->assignMandatory = true;
        session()->flash('academy_assign_ok', 'Kurs zugewiesen und ausgerollt.');
    }

    public function revokeAssignment(int $id): void
    {
        $user = Auth::user();
        $path = $this->resolvePath($user);
        if (!$this->userCanManage($user, $path->team_id)) {
            return;
        }
        $rule = AcademyCourseAssignment::where('team_id', $path->team_id)
            ->where('academy_path_id', $path->id)->find($id);
        if ($rule) {
            app(AcademyAssignmentService::class)->revoke($rule);
        }
    }

    public function resyncAssignment(int $id): void
    {
        $user = Auth::user();
        $path = $this->resolvePath($user);
        if (!$this->userCanManage($user, $path->team_id)) {
            return;
        }
        $rule = AcademyCourseAssignment::where('team_id', $path->team_id)
            ->where('academy_path_id', $path->id)
            ->where('status', AcademyCourseAssignment::STATUS_ACTIVE)->find($id);
        if ($rule) {
            $added = app(AcademyAssignmentService::class)->fanOut($rule);
            session()->flash('academy_assign_ok', "Mitglieder neu aufgelöst — neue Zuweisungen: {$added}.");
        }
    }

    protected function userCanManage($user, int $teamId): bool
    {
        $membership = $user->teams()->where('teams.id', $teamId)->first();
        $role = $membership?->pivot?->role;

        return in_array($role, ['owner', 'admin'], true);
    }

    protected function descendantTeams(Team $team): Collection
    {
        $out = collect();
        foreach ($team->childTeams as $child) {
            $out->push($child);
            $out = $out->merge($this->descendantTeams($child));
        }

        return $out;
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
        $assignment = AcademyUserAssignment::where('user_id', $user->id)
            ->where('academy_path_id', $path->id)
            ->whereIn('status', [
                AcademyUserAssignment::STATUS_ASSIGNED,
                AcademyUserAssignment::STATUS_IN_PROGRESS,
                AcademyUserAssignment::STATUS_OVERDUE,
            ])
            ->orderByRaw('due_at is null, due_at asc')
            ->first();

        // Verwaltung: nur Owner/Admin sehen den Zuweisen-Abschnitt.
        $canManage = $this->userCanManage($user, $path->team_id);
        $manage = null;
        if ($canManage) {
            $team = $user->currentTeam;
            $registry = app(AudienceResolverRegistry::class);

            $rules = AcademyCourseAssignment::where('team_id', $path->team_id)
                ->where('academy_path_id', $path->id)
                ->withCount([
                    'userAssignments as persons_total',
                    'userAssignments as persons_completed' => fn ($q) => $q->where('status', AcademyUserAssignment::STATUS_COMPLETED),
                    'userAssignments as persons_overdue' => fn ($q) => $q->where('status', AcademyUserAssignment::STATUS_OVERDUE),
                ])
                ->orderByDesc('id')
                ->get()
                ->each(fn ($a) => $a->setAttribute(
                    'target_label',
                    $registry->label($a->target_type, (int) $a->target_id, $path->team_id) ?? ($a->target_type . ' #' . $a->target_id)
                ));

            $manage = [
                'rules' => $rules,
                'persons' => $team->users()->orderBy('name')->get(['users.id', 'users.name']),
                'teams' => collect([$team])->merge($this->descendantTeams($team))
                    ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values(),
            ];
        }

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
            'canManage' => $canManage,
            'manage' => $manage,
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
