<?php

namespace App\Models;

use HasinHayder\Tyro\Concerns\HasTyroRoles;
use HasinHayder\TyroLogin\Traits\HasTwoFactorAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasTwoFactorAuth, HasTyroRoles;
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return strtolower($this->role) === 'admin' || $this->name === 'Mohammad Nizam Uddin';
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', $this->name);
        $initials = '';
        foreach ($words as $w) {
            $initials .= strtoupper($w[0] ?? '');
        }

        return substr($initials, 0, 4);
    }
}
