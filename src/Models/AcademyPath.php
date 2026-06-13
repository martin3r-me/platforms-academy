<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Symfony\Component\Uid\UuidV7;

class AcademyPath extends Model
{
    protected $table = 'academy_paths';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'uuid',
        'team_id',
        'created_by_user_id',
        'slug',
        'title',
        'description',
        'icon',
        'color',
        'target_audience',
        'status',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->uuid) {
                $model->uuid = (string) UuidV7::generate();
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(
            AcademyLesson::class,
            'academy_path_lessons',
            'academy_path_id',
            'academy_lesson_id'
        )
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('academy_path_lessons.sort_order');
    }

    public function progressFor(int $userId): array
    {
        $lessons = $this->lessons()->get(['academy_lessons.id']);
        $total = $lessons->count();

        if ($total === 0) {
            return ['total' => 0, 'completed' => 0, 'pct' => 0];
        }

        $completed = AcademyLessonProgress::query()
            ->where('user_id', $userId)
            ->whereIn('academy_lesson_id', $lessons->pluck('id'))
            ->where('status', AcademyLessonProgress::STATUS_COMPLETED)
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pct' => (int) round($completed / $total * 100),
        ];
    }
}
