<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Uid\UuidV7;

class AcademyQuiz extends Model
{
    protected $table = 'academy_quizzes';

    public const DEFAULT_PASS_PCT = 70;

    protected $fillable = [
        'uuid',
        'team_id',
        'academy_lesson_id',
        'title',
        'pass_pct',
        'shuffle_questions',
    ];

    protected $casts = [
        'pass_pct' => 'integer',
        'shuffle_questions' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->uuid) {
                $model->uuid = (string) UuidV7::generate();
            }
        });
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AcademyQuizQuestion::class, 'academy_quiz_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AcademyQuizAttempt::class, 'academy_quiz_id');
    }

    public function passThreshold(): int
    {
        return $this->pass_pct ?: self::DEFAULT_PASS_PCT;
    }

    public function latestAttemptFor(int $userId): ?AcademyQuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->latest('id')
            ->first();
    }

    public function hasPassed(int $userId): bool
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('passed', true)
            ->exists();
    }
}
