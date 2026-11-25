<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = ['fecha','categoria','descripcion','monto'];
    protected $casts = ['fecha' => 'date'];
}
