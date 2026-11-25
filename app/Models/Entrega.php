<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    protected $fillable = ['proveedor_id','fecha','litros','precio_litro','importe'];
    protected $casts = ['fecha' => 'date'];

    public function proveedor() { return $this->belongsTo(Proveedor::class); }
}
