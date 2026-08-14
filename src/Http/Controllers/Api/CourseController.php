<?php

namespace Platform\Academy\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyPath;
use Platform\Core\Http\Controllers\ApiController;

/**
 * Academy Course API — liefert veröffentlichte Kurse (Paths) des Token-Teams.
 *
 * Auth: Bearer-Token (Passport) via `api.auth`. Das Team ergibt sich aus dem
 * `current_team_id` des Token-Users — es werden nur Kurse dieses Teams
 * ausgeliefert. Gedacht für die öffentliche Website (Kurskatalog + Kursdetail).
 *
 * Sichtbarkeit: nur Kurse mit status = published UND public = true
 * (Website-Freigabe). Siehe scopedQuery().
 */
class CourseController extends ApiController
{
    /**
     * GET /api/academy/courses
     * Liste der veröffentlichten Kurse des Token-Teams.
     */
    public function index(Request $request)
    {
        $teamId = $this->teamId();
        if (! $teamId) {
            return $this->error('Kein Team im Token-Kontext.', null, 422);
        }

        $courses = $this->scopedQuery($teamId)
            ->with('category')
            ->withCount(['publishedLessons as lessons_count'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (AcademyPath $path) => $this->formatCourse($path))
            ->all();

        return $this->success($courses, 'Kurse geladen');
    }

    /**
     * GET /api/academy/courses/{uuid}
     * Kursdetail inkl. geordneter, veröffentlichter Lektionen (mit Inhalt).
     */
    public function show(Request $request, string $uuid)
    {
        $teamId = $this->teamId();
        if (! $teamId) {
            return $this->error('Kein Team im Token-Kontext.', null, 422);
        }

        $path = $this->scopedQuery($teamId)
            ->where('uuid', $uuid)
            ->with('category')
            ->first();

        if (! $path) {
            return $this->notFound('Kurs nicht gefunden.');
        }

        $markdown = app(\Platform\Academy\Services\AcademyMarkdownService::class);

        $lessons = $path->publishedLessons()
            ->get()
            ->map(fn (AcademyLesson $lesson) => [
                'uuid' => $lesson->uuid,
                'title' => $lesson->title,
                'summary' => $lesson->summary,
                // Fertig gerendertes HTML inkl. interaktiver Applet-iframes und Alerts.
                'content_html' => $markdown->render($lesson->content),
                'estimated_minutes' => $lesson->estimated_minutes,
                'sort_order' => $lesson->pivot->sort_order ?? $lesson->sort_order,
            ])
            ->all();

        $course = $this->formatCourse($path);
        $course['lessons'] = $lessons;
        $course['lessons_count'] = count($lessons);
        $course['duration_minutes'] = array_sum(array_column($lessons, 'estimated_minutes'));

        return $this->success($course, 'Kurs geladen');
    }

    /**
     * GET /api/academy/courses/health
     * Erreichbarkeits-Check inkl. Beispielkurs.
     */
    public function health(Request $request)
    {
        $teamId = $this->teamId();
        $example = $teamId
            ? $this->scopedQuery($teamId)->with('category')->orderByDesc('id')->first()
            : null;

        return $this->success([
            'status' => 'ok',
            'team_id' => $teamId,
            'example' => $example ? $this->formatCourse($example) : null,
            'timestamp' => now()->toIso8601String(),
        ], 'Academy Course API erreichbar');
    }

    /**
     * Basis-Query: nur veröffentlichte UND für die Website freigegebene Kurse
     * des Teams. `status = published` ist der Redaktions-Status, `public = true`
     * die explizite Freigabe nach außen — beides muss gesetzt sein.
     */
    protected function scopedQuery(int $teamId)
    {
        return AcademyPath::query()
            ->where('team_id', $teamId)
            ->where('status', AcademyPath::STATUS_PUBLISHED)
            ->where('public', true);
    }

    /** Team aus dem authentifizierten Token-User. */
    protected function teamId(): ?int
    {
        $user = Auth::user();

        return $user?->current_team_id ? (int) $user->current_team_id : null;
    }

    /** Kurs in ein flaches, für die Website geeignetes Array überführen. */
    protected function formatCourse(AcademyPath $path): array
    {
        $category = $path->category;

        return [
            'uuid' => $path->uuid,
            'slug' => $path->slug,
            'code' => $path->code,
            'title' => $path->title,
            'description' => $path->description,
            'level' => $path->level,
            'level_label' => $path->levelLabel(),
            'target_audience' => $path->target_audience,
            'icon' => $path->icon,
            'cover_color' => $path->coverColor(),
            'sort_order' => $path->sort_order,
            'lessons_count' => $path->lessons_count ?? null,
            'category' => $category ? [
                'slug' => $category->slug,
                'title' => $category->title,
                'color' => $category->color(),
                'code_prefix' => $category->code_prefix,
            ] : null,
        ];
    }
}
