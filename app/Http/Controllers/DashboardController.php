<?php
namespace App\Http\Controllers;

use App\Models\{Entrega, Gasto, Pago, Produccion, Proveedor, Venta, Distribucion};
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public static function totalesMes(Carbon $ini): array
    {
        $fin = $ini->copy()->endOfMonth();
        $ventas = (float) Venta::whereBetween('fecha', [$ini, $fin])->sum('monto');
        $leche  = (float) Entrega::whereBetween('fecha', [$ini, $fin])->sum('importe'); // costo = entregas, no pagos
        $gastos = (float) Gasto::whereBetween('fecha', [$ini, $fin])->sum('monto');
        return ['ventas' => $ventas, 'leche' => $leche, 'gastos' => $gastos, 'ganancia' => $ventas - $leche - $gastos];
    }

    /** Cifras únicas del mes: las usan Tablero, Gastos y Pagos para que todo coincida. */
    public static function cuadre(Carbon $mes): array
    {
        $t = self::totalesMes($mes);
        $fin = $mes->copy()->endOfMonth();
        $pagadoMes = (float) Pago::whereBetween('fecha', [$mes, $fin])->sum('monto');
        $porPagar = (float) Entrega::sum('importe') - (float) Pago::sum('monto');
        return $t + [
            'egresos'    => $t['leche'] + $t['gastos'],
            'pagado_mes' => $pagadoMes,
            'por_pagar'  => max($porPagar, 0),
            'caja_salio' => $pagadoMes + $t['gastos'],
        ];
    }

    public function index(Request $r)
    {
        $mes = $this->mes($r);
        $t = self::totalesMes($mes);
        $cuadre = self::cuadre($mes);
        // Base 100%: lo vendido, o el total de egresos si se gasta más de lo que se vende (así la barra nunca pasa de 100%).
        $base = max($t['ventas'], $t['leche'] + $t['gastos']);
        $pct = fn ($v) => $base > 0 ? round(max($v, 0) / $base * 100, 1) : 0;
        $perdida = $t['ganancia'] < 0;
        $stock = Produccion::stock();

        $serie = collect(range(5, 0))->map(function ($i) use ($mes) {
            $m = $mes->copy()->subMonths($i);
            $x = self::totalesMes($m);
            return ['mes' => ucfirst($m->locale('es')->translatedFormat('M')), 'ventas' => $x['ventas'], 'egresos' => $x['leche'] + $x['gastos']];
        });

        $zonas = Distribucion::selectRaw('zona, COUNT(*) as cantidad, SUM(monto) as monto')
            ->whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()])
            ->groupBy('zona')->get()->keyBy('zona');
        $deudas = Proveedor::withSum('entregas', 'importe')->withSum('pagos', 'monto')->get()
            ->filter(fn ($p) => $p->saldo > 0.5)->sortByDesc('saldo');

        return view('dashboard', compact('t', 'mes', 'pct', 'perdida', 'stock', 'serie', 'deudas', 'zonas', 'cuadre'));
    }
}
