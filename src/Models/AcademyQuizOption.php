<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Uid\UuidV7;

class AcademyQuizOption extends Model
{
    protected $table = 'academy_quiz_options';

    protected $fillable = [
        'uuid',
        'academy_quiz_question_id',
        'label',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (!$model->uuid) {
                $model->uuid = (string) UuidV7::generate();
            }
        });
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AcademyQuizQuestion::class, 'academy_quiz_question_id');
    }
}
