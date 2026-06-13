<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Uid\UuidV7;

class AcademyTopic extends Model
{
    protected $table = 'academy_topics';

    protected $fillable = [
        'uuid',
        'team_id',
        'created_by_user_id',
        'slug',
        'title',
        'description',
        'icon',
        'color',
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

    public function lessons(): HasMany
    {
        return $this->hasMany(AcademyLesson::class, 'academy_topic_id')->orderBy('sort_order');
    }

    public function publishedLessons(): HasMany
    {
        return $this->lessons()->where('status', AcademyLesson::STATUS_PUBLISHED);
    }
}
