<?php
namespace App\Http\Controllers;

use App\Models\{Pago, Proveedor};
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index(Request $r)
    {
        $mes = $this->mes($r);
        $proveedores = Proveedor::withSum('entregas', 'importe')->withSum('pagos', 'monto')->orderBy('nombre')->get();
        $pagos = Pago::with('proveedor')->whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()])
            ->latest('fecha')->latest('id')->paginate(10)->withQueryString();
        $pagadoMes = (float) Pago::whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()])->sum('monto');
        $entregado = (float) $proveedores->sum(fn ($p) => (float) $p->entregas_sum_importe);
        $pagado = (float) $proveedores->sum(fn ($p) => (float) $p->pagos_sum_monto);
        $pendiente = (float) $proveedores->sum(fn ($p) => max($p->saldo, 0));
        $conDeuda = $proveedores->filter(fn ($p) => $p->saldo > 0.5);
        $seleccionado = (int) $r->query('proveedor') ?: optional($conDeuda->first())->id;
        return view('pagos.index', compact('proveedores', 'pagos', 'mes', 'pagadoMes', 'entregado', 'pagado', 'pendiente', 'seleccionado'));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => $this->reglaFecha(),
            'monto' => 'required|numeric|min:0.01',
            'nota' => 'nullable|string|max:160',
        ], [
            'proveedor_id.required' => 'Selecciona un proveedor.',
            'monto.required' => 'Indica el monto del pago.',
            'monto.min' => 'El monto debe ser mayor que cero.',
        ] + $this->mensajesFecha());
        $p = Proveedor::withSum('entregas', 'importe')->withSum('pagos', 'monto')->findOrFail($d['proveedor_id']);
        if ($d['monto'] > $p->saldo + 0.005) {
            return back()->withInput()->withErrors('El pago supera el saldo de ' . $p->nombre . ': S/ ' . number_format(max($p->saldo, 0), 2));
        }
        Pago::create($d);
        return back()->with('ok', 'Pago registrado: S/ ' . number_format($d['monto'], 2) . ' a ' . $p->nombre);
    }

    public function update(Request $r, Pago $pago)
    {
        $d = $r->validate([
            'fecha' => $this->reglaFecha(),
            'monto' => 'required|numeric|min:0.01',
            'nota' => 'nullable|string|max:160',
        ], ['monto.min' => 'El monto debe ser mayor que cero.'] + $this->mensajesFecha());
        $p = Proveedor::withSum('entregas', 'importe')->withSum('pagos', 'monto')->findOrFail($pago->proveedor_id);
        $disponible = $p->saldo + (float) $pago->monto; // lo que debe sin contar este pago
        if ($d['monto'] > $disponible + 0.005) {
            return back()->withErrors('El pago supera el saldo de ' . $p->nombre . ': S/ ' . number_format(max($disponible, 0), 2));
        }
        $pago->update($d);
        return back()->with('ok', 'Pago actualizado: S/ ' . number_format($d['monto'], 2));
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();
        return back()->with('ok', 'Pago eliminado; el saldo del proveedor se actualizó.');
    }
}
