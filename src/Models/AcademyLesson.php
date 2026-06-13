<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Uid\UuidV7;

class AcademyLesson extends Model
{
    protected $table = 'academy_lessons';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'uuid',
        'team_id',
        'academy_topic_id',
        'created_by_user_id',
        'slug',
        'title',
        'summary',
        'content',
        'estimated_minutes',
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

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AcademyTopic::class, 'academy_topic_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    public function paths(): BelongsToMany
    {
        return $this->belongsToMany(
            AcademyPath::class,
            'academy_path_lessons',
            'academy_lesson_id',
            'academy_path_id'
        )->withPivot('sort_order')->withTimestamps();
    }

    public function progress(): HasMany
    {
        return $this->hasMany(AcademyLessonProgress::class, 'academy_lesson_id');
    }

    public function progressFor(int $userId): ?AcademyLessonProgress
    {
        return $this->progress()->where('user_id', $userId)->first();
    }
}
