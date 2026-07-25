<?php

namespace Platform\Academy\Organization;

use Platform\Organization\Contracts\PersonActivityProvider;
use Platform\Academy\Services\AcademyAssignmentService;
use Illuminate\Support\Facades\Route;

/**
 * Speist Academy-Pflichtkurse in die persönliche Sicht (home) — über den
 * PersonActivityRegistry-Kontrakt, genauso wie planner/helpdesk. Basis ist der
 * academy-eigene Service (mandatoryForUser), kein Fremdzugriff aufs Modell.
 */
class AcademyPersonActivityProvider implements PersonActivityProvider
{
    public function sectionKey(): string
    {
        return 'academy';
    }

    public function sectionConfig(): array
    {
        return [
            'label'       => 'Akademie',
            'icon'        => 'academic-cap',
            'description' => 'Deine Pflichtkurse',
        ];
    }

    public function vitalSigns(int $userId, int $teamId): array
    {
        $courses = $this->courses($userId, $teamId);
        if (empty($courses)) {
            return [];
        }

        $total = count($courses);
        $done = count(array_filter($courses, fn ($c) => $c['is_completed']));
        $overdue = count(array_filter($courses, fn ($c) => $c['is_overdue']));
        $open = $total - $done;

        $variant = $overdue > 0 ? 'danger' : ($open > 0 ? 'warning' : 'success');

        return [
            ['key' => 'pflicht', 'label' => 'Pflichtkurse', 'value' => $done . '/' . $total, 'variant' => $variant],
        ];
    }

    public function metricConfig(): array
    {
        return [
            'pflicht_offen' => ['label' => 'Offene Pflichtkurse', 'type' => 'warning', 'sort_weight' => 6],
        ];
    }

    public function responsibilities(int $userId, int $teamId, int $limit = 5): array
    {
        $courses = $this->courses($userId, $teamId);
        $open = array_values(array_filter($courses, fn ($c) => !$c['is_completed']));

        if (empty($open)) {
            return [];
        }

        $items = [];
        foreach (array_slice($open, 0, $limit) as $i => $c) {
            $items[] = [
                'id'   => $i,
                'name' => $c['title'],
                'url'  => $this->courseUrl($c['path_uuid']),
                'meta' => $this->metaFor($c),
            ];
        }

        return [[
            'key'         => 'pflicht',
            'label'       => 'Offene Pflichtkurse',
            'icon'        => 'academic-cap',
            'total_count' => count($open),
            'items'       => $items,
        ]];
    }

    /** @return array<int, array<string,mixed>> */
    protected function courses(int $userId, int $teamId): array
    {
        try {
            return resolve(AcademyAssignmentService::class)->mandatoryForUser($userId, $teamId);
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function metaFor(array $course): string
    {
        if ($course['is_overdue']) {
            return 'überfällig';
        }
        if ($course['due_at']) {
            return 'fällig ' . \Illuminate\Support\Carbon::parse($course['due_at'])->format('d.m.Y');
        }
        return $course['progress_pct'] . '%';
    }

    protected function courseUrl(?string $uuid): ?string
    {
        if (!$uuid || !Route::has('academy.paths.show')) {
            return null;
        }
        return route('academy.paths.show', ['uuid' => $uuid]);
    }
}
