<?php

namespace Platform\Academy\Livewire\Topic;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyTopic;
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
        $topic = AcademyTopic::query()
            ->where('uuid', $this->uuid)
            ->where('team_id', $user->currentTeam->id)
            ->firstOrFail();

        $lessons = $topic->lessons()
            ->where('status', AcademyLesson::STATUS_PUBLISHED)
            ->get();

        $progress = app(AcademyProgressService::class);
        $completedIds = $progress->completedLessonIdsForUser($user->id, $lessons->pluck('id')->all());
        $completedSet = array_flip($completedIds);

        $this->dispatch('comms', [
            'model' => \Platform\Academy\Models\AcademyTopic::class,
            'modelId' => $topic->id,
            'subject' => 'Academy: ' . $topic->title,
            'description' => $topic->description,
            'url' => route('academy.topics.show', ['uuid' => $topic->uuid]),
            'source' => 'academy.topics.show',
            'recipients' => [],
            'meta' => ['view_type' => 'show', 'resource' => 'topic'],
        ]);

        return view('academy::livewire.topic.show', [
            'topic' => $topic,
            'lessons' => $lessons,
            'completedSet' => $completedSet,
        ])->layout('platform::layouts.app');
    }
}
