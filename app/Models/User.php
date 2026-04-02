<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles; // TAMBAHKAN INI

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // TAMBAHKAN HasRoles

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar',
        'is_active',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'role_name',
        'is_passenger',
        'is_conductor',
        'is_admin',
        'is_super_admin',
    ];

    public function getRoleNames()
    {
        return collect([$this->role_name]);
    }

    public function hasRole($role)
    {
        return $this->role === $role || $this->role_name === $role;
    }

    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    public function getPermissionsViaRoles()
    {
        return collect();
    }

    public function getPermissionNames()
    {
        return collect();
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'kondektur'   => 'Kondektur',
            'penumpang'   => 'Penumpang',
            default       => ucfirst($this->role),
        };
    }

    public function getIsPassengerAttribute(): bool
    {
        return $this->role === 'penumpang';
    }

    public function getIsConductorAttribute(): bool
    {
        return $this->role === 'kondektur';
    }

    public function getIsAdminAttribute(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function getIsSuperAdminAttribute(): bool
    {
        return $this->role === 'super_admin';
    }

    public function scopePassengers($query)
    {
        return $query->where('role', 'penumpang');
    }

    public function scopeConductors($query)
    {
        return $query->where('role', 'kondektur');
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }

    public function scopeSuperAdmins($query)
    {
        return $query->where('role', 'super_admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
