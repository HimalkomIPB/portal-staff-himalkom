<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $title
 * @property \Illuminate\Support\Carbon $date
 * @property string $start_time
 * @property string $end_time
 * @property string $jenis         offline|online
 * @property string|null $lokasi
 * @property string $skala         departemen|general
 * @property string|null $deskripsi
 * @property string|null $department_id
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\User $creator
 */
class Agenda extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'date',
        'start_time',
        'end_time',
        'jenis',
        'lokasi',
        'skala',
        'deskripsi',
        'department_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // -------------------------------------------------------
    // Relations
    // -------------------------------------------------------

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    /**
     * Filter agenda yang boleh dilihat oleh user tertentu.
     * - Agenda General: semua user boleh lihat.
     * - Agenda Departemen: hanya user dari departemen yang sama.
     * - BPH / Supervisor: bisa lihat semua.
     */
    public function scopeVisibleTo($query, User $user)
    {
        // Supervisor & BPH boleh lihat semua
        if ($user->can('agenda.create-org') || $user->hasRole('supervisor')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('skala', 'general');
            if ($user->department_id) {
                $q->orWhere(function ($q2) use ($user) {
                    $q2->where('skala', 'departemen')
                        ->where('department_id', $user->department_id);
                });
            }
        });
    }
}
