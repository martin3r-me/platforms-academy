<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Uid\UuidV7;

class AcademyPath extends Model
{
    protected $table = 'academy_paths';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const LEVEL_BEGINNER = 'beginner';
    public const LEVEL_INTERMEDIATE = 'intermediate';
    public const LEVEL_ADVANCED = 'advanced';

    public const LEVELS = [
        self::LEVEL_BEGINNER => 'Einsteiger',
        self::LEVEL_INTERMEDIATE => 'Fortgeschritten',
        self::LEVEL_ADVANCED => 'Profi',
    ];

    /** Fallback-Cover-Farbe (Platform-Primary), falls weder Kategorie noch Override gesetzt sind. */
    public const DEFAULT_COLOR = '#4F46E5';

    protected $fillable = [
        'uuid',
        'team_id',
        'academy_category_id',
        'created_by_user_id',
        'slug',
        'title',
        'code',
        'level',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(AcademyCategory::class, 'academy_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(AcademyPathEnrollment::class, 'academy_path_id');
    }

    public function enrollmentFor(int $userId): ?AcademyPathEnrollment
    {
        return $this->enrollments()->where('user_id', $userId)->first();
    }

    /**
     * Cover-Farbe: Path-Override > Kategorie-Farbe > Default.
     * Treibt den typografischen Kurs-Cover-Verlauf und das Kategorie-Label.
     */
    public function coverColor(): string
    {
        if ($this->color && str_starts_with($this->color, '#')) {
            return $this->color;
        }

        return $this->relationLoaded('category')
            ? ($this->category?->color ?: self::DEFAULT_COLOR)
            : ($this->category()->first()?->color ?: self::DEFAULT_COLOR);
    }

    public function levelLabel(): ?string
    {
        return $this->level ? (self::LEVELS[$this->level] ?? null) : null;
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
