<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEvaluation extends Model
{
    use HasUlids;

    protected $fillable = [
        'evaluator_id',
        'evaluated_id',
        'department_id',
        'evaluator_role',
        'period_month',
        'period_year',
        'score_attendance',
        'score_commitment',
        'score_contribution',
        'score_initiative',
        'final_score',
        'notes',
    ];

    protected $casts = [
        'score_attendance'   => 'integer',
        'score_commitment'   => 'integer',
        'score_contribution' => 'integer',
        'score_initiative'   => 'integer',
        'final_score'        => 'decimal:2',
        'period_month'       => 'integer',
        'period_year'        => 'integer',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    // Hitung dan set final_score otomatis
    public static function computeFinalScore(int $attendance, int $commitment, int $contribution, int $initiative): float
    {
        return round(($attendance * 0.10) + ($commitment * 0.30) + ($contribution * 0.30) + ($initiative * 0.30), 2);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function evaluated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Cek apakah evaluator sudah pernah menilai member ini di periode tertentu.
     */
    public static function alreadyEvaluated(
        string $evaluatorId,
        string $evaluatedId,
        string $departmentId,
        string $role,
        int $month,
        int $year
    ): bool {
        return static::where([
            'evaluator_id'   => $evaluatorId,
            'evaluated_id'   => $evaluatedId,
            'department_id'  => $departmentId,
            'evaluator_role' => $role,
            'period_month'   => $month,
            'period_year'    => $year,
        ])->exists();
    }
}
