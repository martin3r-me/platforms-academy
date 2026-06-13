<?php

namespace Platform\Academy\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyTopic;

class Sidebar extends Component
{
    public function render()
    {
        $user = Auth::user();

        if (!$user) {
            return view('academy::livewire.sidebar', [
                'paths' => collect(),
                'topics' => collect(),
            ]);
        }

        $teamId = $user->currentTeam->id;

        $paths = AcademyPath::query()
            ->where('team_id', $teamId)
            ->where('status', AcademyPath::STATUS_PUBLISHED)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(10)
            ->get();

        $topics = AcademyTopic::query()
            ->where('team_id', $teamId)
            ->withCount('publishedLessons')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(15)
            ->get();

        return view('academy::livewire.sidebar', [
            'paths' => $paths,
            'topics' => $topics,
        ]);
    }
}
