<?php

namespace Platform\Academy\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyPath;

class Sidebar extends Component
{
    public function render()
    {
        $user = Auth::user();

        if (!$user) {
            return view('academy::livewire.sidebar', [
                'paths' => collect(),
            ]);
        }

        $teamId = $user->currentTeam->id;
        $userId = $user->id;

        $paths = AcademyPath::query()
            ->where('team_id', $teamId)
            ->where('status', AcademyPath::STATUS_PUBLISHED)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(10)
            ->get()
            ->map(function (AcademyPath $path) use ($userId) {
                $summary = $path->progressFor($userId);
                $path->setAttribute('progress_pct', $summary['pct']);
                $path->setAttribute('progress_completed', $summary['completed']);
                $path->setAttribute('progress_total', $summary['total']);
                return $path;
            });

        return view('academy::livewire.sidebar', [
            'paths' => $paths,
        ]);
    }
}
