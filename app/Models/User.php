<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = ['admin' => 'Administrador', 'operador' => 'Operador'];

    protected $fillable = ['name', 'email', 'rol', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function getRolEtiquetaAttribute(): string
    {
        return self::ROLES[$this->rol] ?? ucfirst((string) $this->rol);
    }
}
