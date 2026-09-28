<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'core_users';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'avatar',
        'name',
        'email',
        'password',
        'class_id',
        'academic_year',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['role', 'is_active'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
        ];
    }

    /**
     * Determine if user is active (default true for core_users).
     */
    public function getIsActiveAttribute(): bool
    {
        if (array_key_exists('is_active', $this->attributes) && $this->attributes['is_active'] !== null) {
            return (bool) $this->attributes['is_active'];
        }

        return true;
    }

    /**
     * Dynamically determine role based on eage_prod relationships (ref_students / core_employees / assoc_user_roles).
     */
    public function getRoleAttribute(): string
    {
        if (isset($this->attributes['role']) && ! empty($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        if ($this->email === 'superadmin@smkn1talaga.sch.id' || str_contains(strtolower((string) $this->email), 'admin')) {
            return 'superadmin';
        }

        try {
            $hasSuperAdminRole = DB::table('assoc_user_roles')
                ->join('core_roles', 'assoc_user_roles.role_id', '=', 'core_roles.id')
                ->where('assoc_user_roles.user_id', $this->id)
                ->where(function ($query) {
                    $query->where('core_roles.name', 'LIKE', '%Super Admin%')
                        ->orWhere('core_roles.name', 'LIKE', '%Admin%')
                        ->orWhere('core_roles.name', 'LIKE', '%Panitia%');
                })
                ->exists();

            if ($hasSuperAdminRole) {
                return 'superadmin';
            }
        } catch (\Throwable $e) {
            // Fallback if table query fails
        }

        if ($this->student()->exists()) {
            return 'siswa';
        }

        if ($this->employee()->exists()) {
            return 'guru';
        }

        return 'superadmin';
    }

    /**
     * Get student details if user is student.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id', 'id');
    }

    /**
     * Get employee details if user is teacher/employee.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    /**
     * Get voter accesses assigned to this user.
     */
    public function voterAccesses(): HasMany
    {
        return $this->hasMany(VoterAccessEvote::class, 'user_id', 'id');
    }

    /**
     * Check if user is admin or superadmin.
     */
    public function isAdmin(): bool
    {
        return in_array(strtolower($this->role), ['admin', 'superadmin', 'super admin', 'panitia'], true);
    }

    /**
     * Check if user has administrative rights (admin/panitia/superadmin). Superadmin is automatically committee/panitia.
     */
    public function isCommittee(): bool
    {
        return $this->isAdmin() || in_array(strtolower($this->role), ['admin', 'panitia', 'superadmin', 'super admin'], true);
    }

    /**
     * Check if user is student.
     */
    public function isStudent(): bool
    {
        return strtolower($this->role) === 'siswa';
    }

    /**
     * Check if user is teacher.
     */
    public function isTeacher(): bool
    {
        return strtolower($this->role) === 'guru';
    }
}
