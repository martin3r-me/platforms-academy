<?php

namespace Platform\Academy\Services;

use Illuminate\Support\Str;
use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Models\AcademyTopic;

class AcademyLessonService
{
    public function create(AcademyTopic $topic, int $userId, array $attributes): AcademyLesson
    {
        $slug = $attributes['slug'] ?? Str::slug($attributes['title']);

        return AcademyLesson::create([
            'team_id' => $topic->team_id,
            'academy_topic_id' => $topic->id,
            'created_by_user_id' => $userId,
            'slug' => $this->uniqueSlug($topic->id, $slug),
            'title' => $attributes['title'],
            'summary' => $attributes['summary'] ?? null,
            'content' => $attributes['content'] ?? null,
            'estimated_minutes' => $attributes['estimated_minutes'] ?? null,
            'status' => $attributes['status'] ?? AcademyLesson::STATUS_DRAFT,
            'sort_order' => $attributes['sort_order'] ?? $this->nextSortOrder($topic->id),
        ]);
    }

    public function update(AcademyLesson $lesson, array $attributes): AcademyLesson
    {
        $lesson->fill(array_intersect_key($attributes, array_flip([
            'title', 'summary', 'content', 'estimated_minutes', 'status', 'sort_order',
        ])));
        $lesson->save();

        return $lesson;
    }

    public function publish(AcademyLesson $lesson): AcademyLesson
    {
        $lesson->status = AcademyLesson::STATUS_PUBLISHED;
        $lesson->save();
        return $lesson;
    }

    public function archive(AcademyLesson $lesson): AcademyLesson
    {
        $lesson->status = AcademyLesson::STATUS_ARCHIVED;
        $lesson->save();
        return $lesson;
    }

    public function delete(AcademyLesson $lesson): void
    {
        $lesson->delete();
    }

    protected function uniqueSlug(int $topicId, string $base): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $i = 2;

        while (AcademyLesson::where('academy_topic_id', $topicId)->where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $i++;
        }

        return $candidate;
    }

    protected function nextSortOrder(int $topicId): int
    {
        return (int) AcademyLesson::where('academy_topic_id', $topicId)->max('sort_order') + 10;
    }
}
