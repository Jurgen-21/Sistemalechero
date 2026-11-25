<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Distribucion extends Model {
    protected $table = 'distribuciones';
    protected $fillable = ['fecha','zona','destinatario','producto','cantidad','unidad','monto','estado','observacion'];
    protected $casts = ['fecha' => 'date', 'cantidad' => 'decimal:2', 'monto' => 'decimal:2'];
}
