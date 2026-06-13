<?php

namespace Platform\Academy\Services;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyLessonProgress;
use Platform\Academy\Models\AcademyPath;

class AcademyProgressService
{
    public function start(int $userId, AcademyLesson $lesson): AcademyLessonProgress
    {
        return AcademyLessonProgress::firstOrCreate(
            ['user_id' => $userId, 'academy_lesson_id' => $lesson->id],
            ['status' => AcademyLessonProgress::STATUS_IN_PROGRESS, 'started_at' => now()],
        );
    }

    public function complete(int $userId, AcademyLesson $lesson): AcademyLessonProgress
    {
        $progress = AcademyLessonProgress::firstOrNew([
            'user_id' => $userId,
            'academy_lesson_id' => $lesson->id,
        ]);

        $progress->status = AcademyLessonProgress::STATUS_COMPLETED;
        $progress->started_at ??= now();
        $progress->completed_at = now();
        $progress->save();

        return $progress;
    }

    public function reopen(int $userId, AcademyLesson $lesson): ?AcademyLessonProgress
    {
        $progress = AcademyLessonProgress::where('user_id', $userId)
            ->where('academy_lesson_id', $lesson->id)
            ->first();

        if (!$progress) {
            return null;
        }

        $progress->status = AcademyLessonProgress::STATUS_IN_PROGRESS;
        $progress->completed_at = null;
        $progress->save();

        return $progress;
    }

    public function summaryForPath(int $userId, AcademyPath $path): array
    {
        return $path->progressFor($userId);
    }

    public function completedLessonIdsForUser(int $userId, array $lessonIds): array
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
