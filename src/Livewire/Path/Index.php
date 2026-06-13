<?php

namespace Platform\Academy\Livewire\Path;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyPathService;

class Index extends Component
{
    public function render()
    {
        $user = Auth::user();
        $service = app(AcademyPathService::class);

        $paths = $service->listForTeam($user->currentTeam->id, publishedOnly: true)
            ->map(function (AcademyPath $path) use ($user) {
                $path->setAttribute('progress_pct', $path->progressFor($user->id)['pct']);
                return $path;
            });

        return view('academy::livewire.path.index', [
            'paths' => $paths,
        ])->layout('platform::layouts.app');
    }
}
