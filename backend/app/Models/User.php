<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'users';
    protected $guarded = ['id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso'     => 'datetime',
            'activo'            => 'boolean',
            'password'          => 'hashed',
        ];
    }

    protected string $guard_name = 'web';

    // ── Relationships ─────────────────────────────────────────────────────────
    public function vendedor()
    {
        return $this->hasOne(Vendedor::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
