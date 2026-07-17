<?php

namespace Platform\Academy\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyUserAssignment;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Academy\Services\AcademyEnrollmentService;

class Sidebar extends Component
{
    public function render()
    {
        $user = Auth::user();

        if (!$user) {
            return view('academy::livewire.sidebar', ['courses' => collect(), 'assignments' => collect()]);
        }

        // Nur abonnierte Kurse — sortiert nach letzter Aktivität.
        $courses = app(AcademyEnrollmentService::class)
            ->activeForUser($user->id, $user->currentTeam->id)
            ->map(fn ($row) => [
                'uuid' => $row['path']->uuid,
                'title' => $row['path']->title,
                'icon' => $row['path']->icon,
                'pct' => $row['progress']['pct'],
                'completed' => $row['enrollment']->isCompleted(),
            ])
            ->take(8);

        // Offene Pflicht-/zugewiesene Kurse — nach Deadline sortiert (siehe openForUser).
        $assignments = app(AcademyAssignmentService::class)
            ->openForUser($user->id, $user->currentTeam->id)
            ->map(fn ($ua) => [
                'uuid' => $ua->path?->uuid,
                'title' => $ua->path?->title,
                'due' => $ua->due_at?->format('d.m.'),
                'due_full' => $ua->due_at?->format('d.m.Y'),
                'overdue' => $ua->status === AcademyUserAssignment::STATUS_OVERDUE,
            ])
            ->filter(fn ($a) => $a['uuid'] !== null)
            ->values()
            ->take(8);

        return view('academy::livewire.sidebar', [
            'courses' => $courses,
            'assignments' => $assignments,
        ]);
    }
}
