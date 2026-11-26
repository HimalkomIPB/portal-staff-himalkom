<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotmPj extends Model
{
    protected $fillable = ['department_id', 'user_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isPj(string $userId, string $departmentId): bool
    {
        return static::where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->exists();
    }

    public static function isAnyPj(string $userId): bool
    {
        return static::where('user_id', $userId)->exists();
    }
}
