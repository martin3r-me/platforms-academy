<?php

namespace Platform\Academy\Services;

use Illuminate\Support\Str;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyPath;

class AcademyPathService
{
    public function listForTeam(int $teamId, bool $publishedOnly = false)
    {
        $query = AcademyPath::query()
            ->where('team_id', $teamId)
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('title');

        if ($publishedOnly) {
            $query->where('status', AcademyPath::STATUS_PUBLISHED);
        }

        return $query->get();
    }

    public function create(int $teamId, int $userId, array $attributes): AcademyPath
    {
        $slug = $attributes['slug'] ?? Str::slug($attributes['title']);

        return AcademyPath::create([
            'team_id' => $teamId,
            'created_by_user_id' => $userId,
            'slug' => $this->uniqueSlug($teamId, $slug),
            'title' => $attributes['title'],
            'description' => $attributes['description'] ?? null,
            'icon' => $attributes['icon'] ?? null,
            'color' => $attributes['color'] ?? null,
            'target_audience' => $attributes['target_audience'] ?? null,
            'status' => $attributes['status'] ?? AcademyPath::STATUS_DRAFT,
            'sort_order' => $attributes['sort_order'] ?? $this->nextSortOrder($teamId),
        ]);
    }

    public function update(AcademyPath $path, array $attributes): AcademyPath
    {
        $path->fill(array_intersect_key($attributes, array_flip([
            'title', 'description', 'icon', 'color', 'target_audience', 'status', 'sort_order',
        ])));
        $path->save();

        return $path;
    }

    public function delete(AcademyPath $path): void
    {
        $path->delete();
    }

    public function attachLesson(AcademyPath $path, AcademyLesson $lesson, ?int $sortOrder = null): void
    {
        $sortOrder ??= ($path->lessons()->max('academy_path_lessons.sort_order') ?? 0) + 10;

        $path->lessons()->syncWithoutDetaching([
            $lesson->id => ['sort_order' => $sortOrder],
        ]);
    }

    public function detachLesson(AcademyPath $path, AcademyLesson $lesson): void
    {
        $path->lessons()->detach($lesson->id);
    }

    public function reorderLessons(AcademyPath $path, array $lessonIdsInOrder): void
    {
        foreach (array_values($lessonIdsInOrder) as $index => $lessonId) {
            $path->lessons()->updateExistingPivot($lessonId, [
                'sort_order' => ($index + 1) * 10,
            ]);
        }
    }

    protected function uniqueSlug(int $teamId, string $base): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $i = 2;

        while (AcademyPath::where('team_id', $teamId)->where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $i++;
        }

        return $candidate;
    }

    protected function nextSortOrder(int $teamId): int
    {
        return (int) AcademyPath::where('team_id', $teamId)->max('sort_order') + 10;
    }
}
