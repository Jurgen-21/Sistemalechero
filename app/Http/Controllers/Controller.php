<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

abstract class Controller
{
    /** Mes seleccionado (?mes=YYYY-MM) o el mes actual. */
    protected function mes(Request $r): Carbon
    {
        $valor = preg_match('/^\d{4}-\d{2}$/', (string) $r->query('mes')) ? $r->query('mes') : now()->format('Y-m');
        return Carbon::createFromFormat('Y-m-d', $valor . '-01')->startOfMonth();
    }

    /** Fecha de un hecho que ya ocurrió: ni futura ni absurdamente antigua. */
    protected function reglaFecha(): array
    {
        return ['required', 'date', 'before_or_equal:today', 'after_or_equal:2020-01-01'];
    }

    protected function mensajesFecha(): array
    {
        return [
            'fecha.required'         => 'Indica la fecha.',
            'fecha.date'             => 'La fecha no es válida.',
            'fecha.before_or_equal'  => 'La fecha no puede ser futura: registra solo lo que ya ocurrió (hasta hoy).',
            'fecha.after_or_equal'   => 'La fecha es demasiado antigua: usa una fecha desde el 01/01/2020.',
        ];
    }
}
