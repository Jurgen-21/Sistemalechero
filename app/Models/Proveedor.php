<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $fillable = ['nombre', 'zona', 'telefono', 'fecha_registro', 'precio_litro', 'activo'];
    protected $casts = ['activo' => 'boolean', 'precio_litro' => 'decimal:2', 'fecha_registro' => 'date'];

    public function entregas() { return $this->hasMany(Entrega::class); }
    public function pagos()    { return $this->hasMany(Pago::class); }

    public function scopeBuscar(Builder $q, ?string $texto): Builder
    {
        return $texto ? $q->where(fn ($w) => $w->where('nombre', 'like', "%$texto%")->orWhere('zona', 'like', "%$texto%")) : $q;
    }

    public function scopeEstado(Builder $q, ?string $estado): Builder
    {
        return match ($estado) { 'activo' => $q->where('activo', true), 'inactivo' => $q->where('activo', false), default => $q };
    }

    /** Saldo = entregado - pagado. Usa los withSum de la consulta si existen. */
    public function getTotalEntregadoAttribute(): float
    {
        return (float) ($this->entregas_sum_importe ?? $this->entregas()->sum('importe'));
    }

    public function getSaldoAttribute(): float
    {
        return $this->total_entregado - (float) ($this->pagos_sum_monto ?? $this->pagos()->sum('monto'));
    }
}
