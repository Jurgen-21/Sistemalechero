<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produccion extends Model
{
    protected $table = 'producciones';
    protected $fillable = ['fecha', 'producto', 'litros_usados', 'cantidad', 'nota'];
    protected $casts = ['fecha' => 'date', 'litros_usados' => 'decimal:2', 'cantidad' => 'decimal:2'];

    public const PRODUCTOS = ['Queso' => 'kg', 'Yogur' => 'L'];

    /**
     * Existencias actuales (todo el historial):
     *  Leche = litros recibidos - litros usados en producción - leche vendida
     *  Queso/Yogur = producido - vendido
     * Las distribuciones no descuentan: son envíos de lo ya vendido.
     */
    public static function stock(): array
    {
        $recibida = (float) Entrega::sum('litros');
        $usada = (float) self::sum('litros_usados');
        $vendida = fn (string $p) => (float) Venta::where('producto', $p)->sum('cantidad');
        $hecha = fn (string $p) => (float) self::where('producto', $p)->sum('cantidad');

        return [
            'Leche' => ['stock' => $recibida - $usada - $vendida('Leche'), 'unidad' => 'L'],
            'Queso' => ['stock' => $hecha('Queso') - $vendida('Queso'), 'unidad' => 'kg'],
            'Yogur' => ['stock' => $hecha('Yogur') - $vendida('Yogur'), 'unidad' => 'L'],
        ];
    }
}
