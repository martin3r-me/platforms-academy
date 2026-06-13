<?php

namespace Platform\Academy\Livewire\Path;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyProgressService;

class Show extends Component
{
    public string $uuid;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $user = Auth::user();
        $path = AcademyPath::query()
            ->where('uuid', $this->uuid)
            ->where('team_id', $user->currentTeam->id)
            ->firstOrFail();

        $lessons = $path->lessons()->with('topic')->get();
        $summary = app(AcademyProgressService::class)->summaryForPath($user->id, $path);

        $completedIds = app(AcademyProgressService::class)
            ->completedLessonIdsForUser($user->id, $lessons->pluck('id')->all());
        $completedSet = array_flip($completedIds);

        return view('academy::livewire.path.show', [
            'path' => $path,
            'lessons' => $lessons,
            'summary' => $summary,
            'completedSet' => $completedSet,
        ])->layout('platform::layouts.app');
    }
}
