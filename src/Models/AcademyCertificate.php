<?php

namespace Platform\Academy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Uid\UuidV7;

class AcademyCertificate extends Model
{
    protected $table = 'academy_certificates';

    protected $fillable = [
        'uuid',
        'user_id',
        'academy_path_id',
        'team_id',
        'serial',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
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
}
