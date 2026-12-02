<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InternEvaluation extends Model
{
    protected $fillable = [
        'evaluator_id',
        'intern_id',
        'department_id',
        'period_month',
        'period_year',
        'score_punctuality',
        'score_initiative',
        'score_attendance',
        'score_cooperation',
        'score_professionalism',
        'final_score',
        'notes',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Str::ulid()->toBase32();
            }
        });
    }

    public static function computeFinalScore(
        int $punctuality,
        int $initiative,
        int $attendance,
        int $cooperation,
        int $professionalism,
    ): float {
        return round(($punctuality + $initiative + $attendance + $cooperation + $professionalism) * 0.2, 2);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }
}
