<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = ['proveedor_id','fecha','monto','nota'];
    protected $casts = ['fecha' => 'date'];

    public function proveedor() { return $this->belongsTo(Proveedor::class); }
}
