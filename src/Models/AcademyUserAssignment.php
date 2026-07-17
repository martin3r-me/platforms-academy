<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Uid\UuidV7;

/**
 * Die konkrete, trackbare Zuweisung pro Person (Fan-out einer AcademyCourseAssignment).
 * Verknüpft mit dem bestehenden Enrollment; Status folgt Fortschritt/Deadline.
 */
class AcademyUserAssignment extends Model
{
    protected $table = 'academy_user_assignments';

    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'uuid',
        'team_id',
        'academy_course_assignment_id',
        'user_id',
        'academy_path_id',
        'academy_path_enrollment_id',
        'is_mandatory',
        'starts_at',
        'due_at',
        'status',
        'completed_at',
        'reminded_stage',
        'last_reminded_at',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'starts_at' => 'date',
        'due_at' => 'date',
        'completed_at' => 'datetime',
        'last_reminded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->uuid) {
                $model->uuid = (string) UuidV7::generate();
            }
        });
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AcademyCourseAssignment::class, 'academy_course_assignment_id');
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(AcademyPath::class, 'academy_path_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(AcademyPathEnrollment::class, 'academy_path_enrollment_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_OVERDUE,
        ], true);
    }
}
