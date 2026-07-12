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
            ->with('category')
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
        $code = isset($attributes['code']) ? trim((string) $attributes['code']) : null;

        return AcademyPath::create([
            'team_id' => $teamId,
            'academy_category_id' => $attributes['academy_category_id'] ?? null,
            'created_by_user_id' => $userId,
            'slug' => $this->uniqueSlug($teamId, $slug),
            'title' => $attributes['title'],
            'code' => $code ? $this->uniqueCode($teamId, $code) : null,
            'level' => $this->normalizeLevel($attributes['level'] ?? null),
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
        $data = array_intersect_key($attributes, array_flip([
            'title', 'academy_category_id', 'level', 'description', 'icon', 'color',
            'target_audience', 'status', 'sort_order',
        ]));

        if (array_key_exists('level', $data)) {
            $data['level'] = $this->normalizeLevel($data['level']);
        }

        // Code nur anfassen, wenn uebergeben - und Eindeutigkeit sicherstellen
        // (der eigene bestehende Code darf erhalten bleiben).
        if (array_key_exists('code', $attributes)) {
            $code = trim((string) $attributes['code']);
            $data['code'] = $code === '' ? null : $this->uniqueCode($path->team_id, $code, $path->id);
        }

        $path->fill($data);
        $path->save();

        return $path;
    }

    protected function normalizeLevel(mixed $level): ?string
    {
        if ($level === null || $level === '') {
            return null;
        }

        return array_key_exists($level, AcademyPath::LEVELS) ? $level : null;
    }

    protected function uniqueCode(int $teamId, string $base, ?int $ignoreId = null): string
    {
        $base = strtoupper($base);
        $candidate = $base;
        $i = 2;

        while (
            AcademyPath::where('team_id', $teamId)
                ->where('code', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $i++;
        }

        return $candidate;
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
