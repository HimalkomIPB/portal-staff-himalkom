<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Intern extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'department_id',
        'sub_division',
        'is_active',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Str::ulid()->toBase32();
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(InternEvaluation::class);
    }
}
