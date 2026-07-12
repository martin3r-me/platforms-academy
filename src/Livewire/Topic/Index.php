<?php

namespace Platform\Academy\Livewire\Topic;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Services\AcademyProgressService;
use Platform\Academy\Services\AcademyTopicService;

class Index extends Component
{
    public function rendered(): void
    {
        $this->dispatch('comms', [
            'model' => null, 'modelId' => null,
            'subject' => 'Academy: Themen',
            'description' => 'Bibliothek — alle Lektionen nach Thema',
            'url' => route('academy.topics.index'),
            'source' => 'academy.topics.index',
            'recipients' => [],
            'meta' => ['view_type' => 'index', 'resource' => 'topics'],
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $teamId = $user->currentTeam->id;

        $topics = app(AcademyTopicService::class)->listForTeam($teamId);

        // Fortschritt pro Thema (abgeschlossene veröffentlichte Lektionen).
        $lessonRows = AcademyLesson::query()
            ->where('team_id', $teamId)
            ->where('status', AcademyLesson::STATUS_PUBLISHED)
            ->whereIn('academy_topic_id', $topics->pluck('id'))
            ->get(['id', 'academy_topic_id']);

        $byTopic = $lessonRows->groupBy('academy_topic_id');
        $completedSet = array_flip(
            app(AcademyProgressService::class)
                ->completedLessonIdsForUser($user->id, $lessonRows->pluck('id')->all())
        );

        foreach ($topics as $topic) {
            $ids = ($byTopic[$topic->id] ?? collect())->pluck('id');
            $total = $ids->count();
            $done = $ids->filter(fn ($id) => isset($completedSet[$id]))->count();
            $topic->setAttribute('lesson_total', $total);
            $topic->setAttribute('lesson_done', $done);
            $topic->setAttribute('progress_pct', $total > 0 ? (int) round($done / $total * 100) : 0);
        }

        return view('academy::livewire.topic.index', [
            'topics' => $topics,
            'lessonsTotal' => $lessonRows->count(),
            'completedTotal' => count($completedSet),
        ])->layout('platform::layouts.app');
    }
}
