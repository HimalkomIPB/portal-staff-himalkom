<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property string $id
 * @property string $name
 * @property string $abbreviation
 * @property string $slug
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $managing_director
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkProgram> $workPrograms
 * @property-read int|null $work_programs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereAbbreviation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withoutTrashed()
 * @mixin \Eloquent
 */
class Department extends Model
{
    use HasUlids, SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'abbreviation',
        'slug',
        'description',
    ];

    public function getManagingDirectorAttribute()
    {
        return $this->users()
            ->whereHas('roles', fn($q) => $q->where('name', 'managing director'))
            ->select(['id', 'name', 'email'])
            ->first();
    }

    public function getPjsAttribute()
    {
        return $this->users()
            ->whereHas('roles', fn($q) => $q->where('name', 'pjs'))
            ->select(['id', 'name', 'email'])
            ->get();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function workPrograms(): HasMany
    {
        return $this->hasMany(WorkProgram::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Str::ulid()->toBase32(); // Generate ULID
            }

            if (!$model->slug) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }
}
