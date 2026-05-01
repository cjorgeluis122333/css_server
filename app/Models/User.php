<?php

namespace App\Models;

use App\Enum\UserRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $fillable = [
        'acc',
        'correo',
        'cedula',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'acc' => 'integer',
            'role' => UserRole::class,
        ];
    }

    public function familyMembers()
    {
        return $this->hasMany(Partner::class, 'acc', 'acc');
    }

    public function isSuperAdmin(): bool
    {
        return $this->acc === 1000;
    }

    public function isAdmin(): bool
    {
        return $this->acc >= 991 && $this->acc <= 999;
    }

    public function isOperator(): bool
    {
        return $this->acc >= 961 && $this->acc <= 990;
    }

    public function isSupervisor(): bool
    {
        return $this->acc >= 931 && $this->acc <= 960;
    }

    public function isAlly(): bool
    {
        return $this->acc >= 901 && $this->acc <= 930;
    }

    public function isHonorary(): bool
    {
        return $this->acc >= 801 && $this->acc <= 900;
    }

    public function isPartner(): bool
    {
        return $this->acc >= 1 && $this->acc <= 800;
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array(UserRole::fromAcc($this->acc), $roles);
    }
}
