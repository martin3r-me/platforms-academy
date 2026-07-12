<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Uid\UuidV7;

class AcademyPathEnrollment extends Model
{
    protected $table = 'academy_path_enrollments';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'uuid',
        'user_id',
        'academy_path_id',
        'team_id',
        'status',
        'enrolled_at',
        'completed_at',
        'last_lesson_id',
        'last_activity_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->uuid) {
                $model->uuid = (string) UuidV7::generate();
            }
        });
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(AcademyPath::class, 'academy_path_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function lastLesson(): BelongsTo
    {
        return $this->belongsTo(AcademyLesson::class, 'last_lesson_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
