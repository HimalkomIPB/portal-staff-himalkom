<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SotmPj extends Model
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

    /**
     * Cek apakah user adalah PJ untuk departemen tertentu (atau PJ umum jika department_id null).
     */
    public static function isPj(string $userId, string $departmentId): bool
    {
        return static::where('user_id', $userId)
            ->where(function ($q) use ($departmentId) {
                // PJ bisa lihat semua divisi (department_id sesuai divisinya)
                // atau jika di masa depan mau support "PJ universal", tambahkan di sini
                $q->where('department_id', $departmentId);
            })
            ->exists();
    }

    /**
     * Cek apakah user terdaftar sebagai PJ manapun.
     */
    public static function isAnyPj(string $userId): bool
    {
        return static::where('user_id', $userId)->exists();
    }
}
