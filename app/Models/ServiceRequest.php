<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = ['id'];

    protected $casts = [
        'due_date' => 'date',
        'is_approved_by_requester' => 'boolean',
    ];

    /**
     * Get the user who requested this service.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Get the department of the requester.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the assigned MDs for this service.
     */
    public function assignees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_request_assignees');
    }

    /**
     * Get the comments/revisions for this service request.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ServiceRequestComment::class);
    }

    /**
     * Get a human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'in_progress' => 'Sedang Dikerjakan',
            'revision' => 'Revisi',
            'uploaded' => 'Diunggah',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Get the color class associated with the status.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'accepted' => 'bg-blue-100 text-blue-800 border-blue-200',
            'rejected' => 'bg-red-100 text-red-800 border-red-200',
            'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'revision' => 'bg-orange-100 text-orange-800 border-orange-200',
            'uploaded' => 'bg-purple-100 text-purple-800 border-purple-200',
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'cancelled' => 'bg-slate-100 text-slate-800 border-slate-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    /**
     * Check if this service type belongs to Divisi Kreatif.
     */
    public function isKreatifService(): bool
    {
        return in_array($this->type, ['copm', 'codm']);
    }

    /**
     * Check if this service type belongs to Divisi RnT.
     */
    public function isRnTService(): bool
    {
        return in_array($this->type, ['komnews', 'riset']);
    }
}
