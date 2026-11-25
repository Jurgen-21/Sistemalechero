<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = ['fecha','producto','cantidad','monto'];
    protected $casts = ['fecha' => 'date'];
}
