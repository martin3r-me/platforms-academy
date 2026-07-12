<?php

namespace Platform\Academy\Services;

use Illuminate\Support\Collection;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyLessonProgress;
use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Models\AcademyPathEnrollment;

class AcademyEnrollmentService
{
    /**
     * Schreibt einen User bewusst in einen Kurs (Path) ein. Idempotent.
     */
    public function enroll(int $userId, AcademyPath $path): AcademyPathEnrollment
    {
        $enrollment = AcademyPathEnrollment::firstOrCreate(
            ['user_id' => $userId, 'academy_path_id' => $path->id],
            [
                'team_id' => $path->team_id,
                'status' => AcademyPathEnrollment::STATUS_ACTIVE,
                'enrolled_at' => now(),
                'last_activity_at' => now(),
            ],
        );

        // Falls direkt beim Einschreiben schon alles erledigt ist.
        $this->refreshCompletion($enrollment, $path);

        return $enrollment;
    }

    /**
     * Beendet die Einschreibung ("Kurs verlassen"). Der Lesson-Fortschritt bleibt erhalten.
     */
    public function drop(int $userId, AcademyPath $path): void
    {
        AcademyPathEnrollment::where('user_id', $userId)
            ->where('academy_path_id', $path->id)
            ->delete();
    }

    public function isEnrolled(int $userId, AcademyPath $path): bool
    {
        return AcademyPathEnrollment::where('user_id', $userId)
            ->where('academy_path_id', $path->id)
            ->exists();
    }

    /**
     * Aktive & abgeschlossene Einschreibungen eines Users, angereichert mit
     * Fortschritt und Resume-Lesson. Sortiert nach letzter Aktivitaet.
     *
     * @return Collection<int, array>
     */
    public function activeForUser(int $userId, int $teamId): Collection
    {
        return AcademyPathEnrollment::query()
            ->where('user_id', $userId)
            ->where('team_id', $teamId)
            ->with(['path.category'])
            ->orderByRaw('last_activity_at is null, last_activity_at desc')
            ->get()
            ->filter(fn (AcademyPathEnrollment $e) => $e->path !== null)
            ->map(function (AcademyPathEnrollment $e) use ($userId) {
                $progress = $e->path->progressFor($userId);

                return [
                    'enrollment' => $e,
                    'path' => $e->path,
                    'progress' => $progress,
                    'resume' => $this->resumeLesson($e),
                ];
            })
            ->values();
    }

    /**
     * Bestimmt die Lesson, bei der der User weitermachen soll:
     * gemerkter Resume-Punkt > erste nicht abgeschlossene Lesson > erste Lesson.
     */
    public function resumeLesson(AcademyPathEnrollment $enrollment): ?AcademyLesson
    {
        $path = $enrollment->path ?? AcademyPath::find($enrollment->academy_path_id);
        if (!$path) {
            return null;
        }

        $lessons = $path->publishedLessons()->get();
        if ($lessons->isEmpty()) {
            return null;
        }

        if ($enrollment->last_lesson_id) {
            $last = $lessons->firstWhere('id', $enrollment->last_lesson_id);
            if ($last) {
                return $last;
            }
        }

        $completedIds = $this->completedLessonIds($enrollment->user_id, $lessons->pluck('id')->all());
        $firstOpen = $lessons->first(fn (AcademyLesson $l) => !in_array($l->id, $completedIds, true));

        return $firstOpen ?? $lessons->first();
    }

    /**
     * Aktualisiert den Resume-Punkt fuer alle Kurse, in die der User eingeschrieben
     * ist und die diese Lesson enthalten. Wird beim Oeffnen einer Lesson aufgerufen.
     */
    public function touch(int $userId, AcademyLesson $lesson): void
    {
        $pathIds = $lesson->paths()->pluck('academy_paths.id');
        if ($pathIds->isEmpty()) {
            return;
        }

        AcademyPathEnrollment::where('user_id', $userId)
            ->whereIn('academy_path_id', $pathIds)
            ->update([
                'last_lesson_id' => $lesson->id,
                'last_activity_at' => now(),
            ]);
    }

    /**
     * Prueft nach Abschluss einer Lesson, ob dadurch ein eingeschriebener Kurs
     * vollstaendig wurde, und markiert die Einschreibung ggf. als abgeschlossen.
     */
    public function syncCompletion(int $userId, AcademyLesson $lesson): void
    {
        $pathIds = $lesson->paths()->pluck('academy_paths.id');
        if ($pathIds->isEmpty()) {
            return;
        }

        $enrollments = AcademyPathEnrollment::where('user_id', $userId)
            ->whereIn('academy_path_id', $pathIds)
            ->with('path')
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->path) {
                $this->refreshCompletion($enrollment, $enrollment->path);
            }
        }
    }

    /**
     * Setzt den Status einer Einschreibung anhand des aktuellen Fortschritts.
     */
    protected function refreshCompletion(AcademyPathEnrollment $enrollment, AcademyPath $path): void
    {
        $progress = $path->progressFor($enrollment->user_id);
        $isComplete = $progress['total'] > 0 && $progress['completed'] >= $progress['total'];

        if ($isComplete && !$enrollment->isCompleted()) {
            $enrollment->status = AcademyPathEnrollment::STATUS_COMPLETED;
            $enrollment->completed_at = now();
            $enrollment->save();
        } elseif (!$isComplete && $enrollment->isCompleted()) {
            // Kurs wurde erweitert oder Lesson wieder geoeffnet -> zurueck auf aktiv.
            $enrollment->status = AcademyPathEnrollment::STATUS_ACTIVE;
            $enrollment->completed_at = null;
            $enrollment->save();
        }
    }

    /**
     * @param  array<int, int>  $lessonIds
     * @return array<int, int>
     */
    protected function completedLessonIds(int $userId, array $lessonIds): array
    {
        if (empty($lessonIds)) {
            return [];
        }

        return AcademyLessonProgress::query()
            ->where('user_id', $userId)
            ->whereIn('academy_lesson_id', $lessonIds)
            ->where('status', AcademyLessonProgress::STATUS_COMPLETED)
            ->pluck('academy_lesson_id')
            ->all();
    }
}
