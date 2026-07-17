<?php

namespace Platform\Academy\Livewire\Assignment;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyCourseAssignment;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyUserAssignment;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Core\Models\Team;
use Platform\Core\Registry\AudienceResolverRegistry;

class Index extends Component
{
    public ?int $pathId = null;
    public string $targetType = 'team';
    public ?int $targetId = null;
    public bool $isMandatory = true;
    public ?string $startsAt = null;
    public ?string $dueAt = null;
    public bool $includeSubteams = false;
    public string $note = '';

    protected function rules(): array
    {
        return [
            'pathId' => ['required', 'integer'],
            'targetType' => ['required', 'in:user,team'],
            'targetId' => ['required', 'integer'],
            'startsAt' => ['nullable', 'date'],
            'dueAt' => ['nullable', 'date'],
        ];
    }

    public function updatedTargetType(): void
    {
        $this->targetId = null;
    }

    public function create(): void
    {
        $this->validate();

        $user = Auth::user();
        $teamId = $user->currentTeam->id;
        $path = AcademyPath::where('team_id', $teamId)->findOrFail($this->pathId);

        app(AcademyAssignmentService::class)->assign(
            $path,
            $this->targetType,
            (int) $this->targetId,
            ($this->targetType === 'team' && $this->includeSubteams) ? ['include_subteams' => true] : [],
            $user->id,
            [
                'is_mandatory' => $this->isMandatory,
                'starts_at' => $this->startsAt ?: null,
                'due_at' => $this->dueAt ?: null,
                'note' => $this->note ?: null,
            ],
        );

        $this->reset(['pathId', 'targetId', 'startsAt', 'dueAt', 'note', 'includeSubteams']);
        $this->isMandatory = true;
        $this->targetType = 'team';
        session()->flash('academy_assignment_ok', 'Zuweisung erstellt und an die Personen ausgerollt.');
    }

    public function revoke(int $id): void
    {
        $rule = AcademyCourseAssignment::where('team_id', Auth::user()->currentTeam->id)->find($id);
        if ($rule) {
            app(AcademyAssignmentService::class)->revoke($rule);
        }
    }

    public function resync(int $id): void
    {
        $rule = AcademyCourseAssignment::where('team_id', Auth::user()->currentTeam->id)
            ->where('status', AcademyCourseAssignment::STATUS_ACTIVE)
            ->find($id);
        if ($rule) {
            $added = app(AcademyAssignmentService::class)->fanOut($rule);
            session()->flash('academy_assignment_ok', "Mitglieder neu aufgelöst — neue Zuweisungen: {$added}.");
        }
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;
        $teamId = $team->id;

        $paths = AcademyPath::where('team_id', $teamId)
            ->where('status', AcademyPath::STATUS_PUBLISHED)
            ->orderBy('title')
            ->get(['id', 'title', 'code']);

        $persons = $team->users()->orderBy('name')->get(['users.id', 'users.name']);

        $teams = collect([$team])->merge($this->descendantTeams($team))
            ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
            ->values();

        $registry = app(AudienceResolverRegistry::class);

        $assignments = AcademyCourseAssignment::where('team_id', $teamId)
            ->with('path:id,title,code')
            ->withCount([
                'userAssignments as persons_total',
                'userAssignments as persons_completed' => fn ($q) => $q->where('status', AcademyUserAssignment::STATUS_COMPLETED),
                'userAssignments as persons_overdue' => fn ($q) => $q->where('status', AcademyUserAssignment::STATUS_OVERDUE),
            ])
            ->orderByDesc('id')
            ->get()
            ->each(function (AcademyCourseAssignment $a) use ($registry, $teamId) {
                $a->setAttribute(
                    'target_label',
                    $registry->label($a->target_type, (int) $a->target_id, $teamId) ?? ($a->target_type . ' #' . $a->target_id)
                );
            });

        return view('academy::livewire.assignment.index', [
            'paths' => $paths,
            'persons' => $persons,
            'teams' => $teams,
            'assignments' => $assignments,
        ])->layout('platform::layouts.app');
    }

    private function descendantTeams(Team $team): Collection
    {
        $out = collect();
        foreach ($team->childTeams as $child) {
            $out->push($child);
            $out = $out->merge($this->descendantTeams($child));
        }

        return $out;
    }
}
