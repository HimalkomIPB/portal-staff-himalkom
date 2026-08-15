<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $department_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkProgramComment> $workProgramComments
 * @property-read int|null $work_program_comments_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasUlids, Notifiable, SoftDeletes {
        HasRoles::hasRole as traitHasRole;
        HasRoles::hasAnyRole as traitHasAnyRole;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'sub_division',
        'is_active',
        'email_verified_at',
    ];

    protected $with = ['department', 'roles'];

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isSuperAdmin(): bool
    {
        return (str_ends_with($this->email, '@himalkom.com') && $this->hasVerifiedEmail())
            || $this->roles->pluck('name')->contains('superadmin');
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->traitHasRole($roles, $guard);
    }

    public function hasAnyRole(...$roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->traitHasAnyRole(...$roles);
    }

    public function getDashboardRoute(): string
    {
        if ($this->isSuperAdmin()) {
            if ($this->department) {
                return route('dashboard', ['department' => $this->department]);
            }

            $firstDepartment = Department::orderBy('name')->first();
            if ($firstDepartment) {
                return route('dashboard', ['department' => $firstDepartment]);
            }

            return route('dashboard.supervisor');
        }

        $roles = $this->pluckRoleNames();
        if ($roles->contains('supervisor')) {
            return route('filament.superadmin.pages.dashboard');
        }

        if ($roles->contains('managing director') || $roles->contains('pjs') ||
            $roles->contains('sekretaris') || $roles->contains('bendahara') ||
            $roles->contains('anggota')) {
            return route('dashboard', ['department' => $this->department]);
        }

        if ($roles->contains('bph') || $roles->contains('sc')) {
            return route('dashboard', ['department' => $this->department]);
        }

        return route('welcome');
    }

    public function getRoleNameForTitle(): string
    {
        if ($this->isSuperAdmin()) {
            return $this->department ? 'Superadmin ('.$this->department->name.')' : 'Superadmin';
        }

        $roles = $this->pluckRoleNames();

        if ($roles->contains('supervisor')) {
            return 'Supervisor';
        } elseif ($roles->contains('bph')) {
            return 'Badan Pengurus Harian';
        } elseif ($roles->contains('managing director')) {
            return 'Managing Director of '.($this->department?->name ?? '-');
        } elseif ($roles->contains('pjs')) {
            return 'PJS of '.($this->department?->name ?? '-');
        } elseif ($roles->contains('sc')) {
            return 'Steering Committee';
        } elseif ($roles->contains('sekretaris')) {
            return 'Sekretaris '.($this->department?->name ?? '');
        } elseif ($roles->contains('bendahara')) {
            return 'Bendahara '.($this->department?->name ?? '');
        } elseif ($roles->contains('anggota')) {
            return 'Anggota '.($this->department?->name ?? '');
        } elseif ($roles->contains('supervisor')) {
            return 'Supervisor';
        } else {
            return '-';
        }
    }

    public function workProgramComments(): HasMany
    {
        return $this->hasMany(WorkProgramComment::class);
    }

    /**
     * Department-department yang diawasi user ini sebagai SC.
     * (Berbeda dari department utama yang ada di kolom department_id)
     */
    public function scDepartments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'sc_assignments')
            ->withTimestamps();
    }

    /**
     * Cek apakah user ini adalah SC untuk department tertentu.
     */
    public function isSCOf(Department $department): bool
    {
        return $this->scDepartments()->where('department_id', $department->id)->exists();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function pluckRoleNames()
    {
        return $this->roles->pluck('name');
    }

    public function pluckRoleName(string $role): string
    {
        $roles = $this->pluckRoleNames();
        if ($roles->contains($role)) {
            return $role;
        } else {
            return '-';
        }
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Str::ulid()->toBase32(); // Generate ULID
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->is_active) {
            return false;
        }
        return $this->hasRole('supervisor') || (str_ends_with($this->email, '@himalkom.com') && $this->hasVerifiedEmail());
    }
}
