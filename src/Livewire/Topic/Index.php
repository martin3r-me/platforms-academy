<?php

namespace Platform\Academy\Livewire\Topic;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Services\AcademyTopicService;

class Index extends Component
{
    public function rendered(): void
    {
        $this->dispatch('comms', [
            'model' => null, 'modelId' => null,
            'subject' => 'Academy: Themen',
            'description' => 'Übersicht aller Themen-Cluster',
            'url' => route('academy.topics.index'),
            'source' => 'academy.topics.index',
            'recipients' => [],
            'meta' => ['view_type' => 'index', 'resource' => 'topics'],
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $service = app(AcademyTopicService::class);

        $topics = $service->listForTeam($user->currentTeam->id);

        return view('academy::livewire.topic.index', [
            'topics' => $topics,
        ])->layout('platform::layouts.app');
    }
}
