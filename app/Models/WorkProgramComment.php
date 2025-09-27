<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $work_program_id
 * @property string $user_id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $author
 * @property-read \App\Models\WorkProgram $workProgram
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkProgramComment whereWorkProgramId($value)
 *
 * @mixin \Eloquent
 */
class WorkProgramComment extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'work_program_id',
        'user_id',
        'content',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workProgram(): BelongsTo
    {
        return $this->belongsTo(WorkProgram::class, 'work_program_id');
    }

    protected function createdAt(): Attribute
    {
        return Attribute::get(fn ($value) => \Carbon\Carbon::parse($value)->format('d M Y H:i'));
    }

    protected static function boot()
    {

        parent::boot();
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Str::ulid()->toBase32();
            }
        });
    }
}
