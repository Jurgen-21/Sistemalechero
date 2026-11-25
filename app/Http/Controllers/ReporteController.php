<?php
namespace App\Http\Controllers;

use App\Models\{Distribucion, Entrega, Gasto, Pago, Produccion, Proveedor, Venta};
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    /** Conjuntos de datos que se pueden bajar como CSV (abre directo en Excel). */
    private function conjuntos(): array
    {
        return [
            'entregas' => ['Entregas de leche', Entrega::with('proveedor'), ['Fecha', 'Proveedor', 'Litros', 'Precio por litro', 'Importe'],
                fn ($e) => [$e->fecha->format('Y-m-d'), $e->proveedor->nombre ?? '', $e->litros, $e->precio_litro, $e->importe]],
            'pagos' => ['Pagos a proveedores', Pago::with('proveedor'), ['Fecha', 'Proveedor', 'Monto', 'Nota'],
                fn ($p) => [$p->fecha->format('Y-m-d'), $p->proveedor->nombre ?? '', $p->monto, $p->nota]],
            'ventas' => ['Ventas', Venta::query(), ['Fecha', 'Producto', 'Cantidad', 'Monto'],
                fn ($v) => [$v->fecha->format('Y-m-d'), $v->producto, $v->cantidad, $v->monto]],
            'gastos' => ['Gastos operativos', Gasto::query(), ['Fecha', 'Categoría', 'Descripción', 'Monto'],
                fn ($g) => [$g->fecha->format('Y-m-d'), $g->categoria, $g->descripcion, $g->monto]],
            'distribuciones' => ['Distribuciones', Distribucion::query(), ['Fecha', 'Zona', 'Destinatario', 'Producto', 'Cantidad', 'Unidad', 'Importe', 'Estado', 'Observación'],
                fn ($d) => [$d->fecha->format('Y-m-d'), $d->zona, $d->destinatario, $d->producto, $d->cantidad, $d->unidad, $d->monto, $d->estado, $d->observacion]],
            'produccion' => ['Producción', Produccion::query(), ['Fecha', 'Producto', 'Litros de leche usados', 'Cantidad obtenida', 'Nota'],
                fn ($p) => [$p->fecha->format('Y-m-d'), $p->producto, $p->litros_usados, $p->cantidad, $p->nota]],
        ];
    }

    public function index(Request $r)
    {
        $mes = $this->mes($r);
        $fin = $mes->copy()->endOfMonth();
        $cuadre = DashboardController::cuadre($mes);

        $ventas = Venta::whereBetween('fecha', [$mes, $fin])->selectRaw('producto, SUM(cantidad) as cantidad, SUM(monto) as monto')->groupBy('producto')->get();
        $gastos = Gasto::whereBetween('fecha', [$mes, $fin])->selectRaw('categoria, SUM(monto) as monto')->groupBy('categoria')->get();
        $proveedores = Proveedor::withSum('entregas', 'importe')->withSum('pagos', 'monto')
            ->withSum(['entregas as litros_mes' => fn ($q) => $q->whereBetween('fecha', [$mes, $fin])], 'litros')
            ->withSum(['entregas as importe_mes' => fn ($q) => $q->whereBetween('fecha', [$mes, $fin])], 'importe')
            ->withSum(['pagos as pagado_mes' => fn ($q) => $q->whereBetween('fecha', [$mes, $fin])], 'monto')
            ->orderBy('nombre')->get();
        $zonas = Distribucion::whereBetween('fecha', [$mes, $fin])->selectRaw('zona, COUNT(*) as n, SUM(monto) as monto')->groupBy('zona')->get();
        $stock = Produccion::stock();
        $tipos = collect($this->conjuntos())->map(fn ($c) => $c[0]);

        return view('reportes.index', compact('mes', 'cuadre', 'ventas', 'gastos', 'proveedores', 'zonas', 'stock', 'tipos'));
    }

    public function exportar(Request $r, string $tipo)
    {
        $c = $this->conjuntos()[$tipo] ?? abort(404);
        [$titulo, $q, $cabecera, $fila] = $c;
        $todo = $r->query('mes') === 'todo';
        if (! $todo) {
            $mes = $this->mes($r);
            $q->whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()]);
        }
        $nombre = "{$tipo}-" . ($todo ? 'completo' : $mes->format('Y-m')) . '.csv';

        return $this->csv($nombre, $cabecera, function ($out) use ($q, $fila) {
            foreach ($q->orderBy('fecha')->cursor() as $x) {
                fputcsv($out, array_map([$this, 'seguro'], $fila($x)));
            }
        });
    }

    public function estadoCuenta(Request $r, Proveedor $proveedor)
    {
        $mov = collect();
        foreach ($proveedor->entregas()->get() as $e) {
            $mov->push(['fecha' => $e->fecha, 'tipo' => 'Entrega', 'detalle' => number_format($e->litros, 1) . ' L × S/ ' . number_format($e->precio_litro, 2), 'cargo' => (float) $e->importe, 'abono' => 0.0, 'o' => 0]);
        }
        foreach ($proveedor->pagos()->get() as $p) {
            $mov->push(['fecha' => $p->fecha, 'tipo' => 'Pago', 'detalle' => $p->nota ?: '—', 'cargo' => 0.0, 'abono' => (float) $p->monto, 'o' => 1]);
        }
        $saldo = 0.0;
        $mov = $mov->sortBy(fn ($m) => $m['fecha']->format('Ymd') . $m['o'])->values()->map(function ($m) use (&$saldo) {
            $saldo += $m['cargo'] - $m['abono'];
            return $m + ['saldo' => $saldo];
        });

        if ($r->boolean('csv')) {
            return $this->csv('estado-cuenta-' . str($proveedor->nombre)->slug() . '.csv', ['Fecha', 'Tipo', 'Detalle', 'Entregado', 'Pagado', 'Saldo'], function ($out) use ($mov) {
                foreach ($mov as $m) {
                    fputcsv($out, [$m['fecha']->format('Y-m-d'), $m['tipo'], $this->seguro($m['detalle']), $m['cargo'], $m['abono'], round($m['saldo'], 2)]);
                }
            });
        }
        return view('reportes.cuenta', ['proveedor' => $proveedor, 'mov' => $mov, 'saldo' => $saldo]);
    }

    private function csv(string $nombre, array $cabecera, callable $cuerpo): StreamedResponse
    {
        return response()->streamDownload(function () use ($cabecera, $cuerpo) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel respete las tildes
            fputcsv($out, $cabecera);
            $cuerpo($out);
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita que Excel interprete texto como fórmula (=, +, -, @). */
    public function seguro($v)
    {
        return is_string($v) && $v !== '' && str_contains("=+-@\t\r", $v[0]) ? "'" . $v : $v;
    }
}
