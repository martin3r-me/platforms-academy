<?php

namespace Platform\Academy\Livewire\Path;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyPathEnrollment;
use Platform\Academy\Services\AcademyCategoryService;

class Index extends Component
{
    #[Url(as: 'category', history: true)]
    public ?string $categorySlug = null;

    public function rendered(): void
    {
        $this->dispatch('comms', [
            'model' => null, 'modelId' => null,
            'subject' => 'Academy: Kurse',
            'description' => 'Kurskatalog',
            'url' => route('academy.paths.index'),
            'source' => 'academy.paths.index',
            'recipients' => [],
            'meta' => ['view_type' => 'index', 'resource' => 'paths'],
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $teamId = $user->currentTeam->id;

        $categories = app(AcademyCategoryService::class)->listForTeam($teamId);
        $activeCategory = $this->categorySlug
            ? $categories->firstWhere('slug', $this->categorySlug)
            : null;

        $paths = AcademyPath::query()
            ->where('team_id', $teamId)
            ->where('status', AcademyPath::STATUS_PUBLISHED)
            ->when($activeCategory, fn ($q) => $q->where('academy_category_id', $activeCategory->id))
            ->with('category')
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function (AcademyPath $path) use ($user) {
                $path->setAttribute('progress_pct', $path->progressFor($user->id)['pct']);
                return $path;
            });

        $enrolledSet = array_flip(
            AcademyPathEnrollment::query()
                ->where('user_id', $user->id)
                ->where('team_id', $teamId)
                ->pluck('academy_path_id')
                ->all()
        );

        return view('academy::livewire.path.index', [
            'paths' => $paths,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'enrolledSet' => $enrolledSet,
        ])->layout('platform::layouts.app');
    }
}
