<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPpc(): bool
    {
        return $this->role === 'ppc';
    }

    public function isSpv(): bool
    {
        return $this->role === 'spv';
    }

    public function isKorlap(): bool
    {
        return $this->role === 'korlap';
    }

    public function canInputTarget(): bool
    {
        return in_array($this->role, ['admin', 'ppc']);
    }

    public function canInputActual(): bool
    {
        return in_array($this->role, ['admin', 'spv', 'korlap']);
    }
}
