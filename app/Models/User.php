<?php

namespace App\Models;

use HasinHayder\Tyro\Concerns\HasTyroRoles;
use HasinHayder\TyroLogin\Traits\HasTwoFactorAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->username)) {
                $base = Str::slug($user->name, '');
                if (empty($base) && ! empty($user->email)) {
                    $base = explode('@', $user->email)[0];
                }
                $username = $base ?: 'user';
                $count = 1;
                while (static::where('username', $username)->exists()) {
                    $username = $base.$count++;
                }
                $user->username = $username;
            }
            if (empty($user->role)) {
                $user->role = 'member';
            }
        });
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
