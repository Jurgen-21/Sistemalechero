<?php
namespace App\Http\Controllers;

use App\Models\{Entrega, Pago, Proveedor};
use Illuminate\Http\Request;

class EntregaController extends Controller
{
    public function index(Request $r)
    {
        $mes = $this->mes($r);
        $q = Entrega::with('proveedor')->whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()]);
        $total = (clone $q)->sum('importe');
        $litros = (clone $q)->sum('litros');
        $numero = (clone $q)->count();
        $entregas = $q->latest('fecha')->latest('id')->paginate(12)->withQueryString();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $seleccionado = $proveedores->firstWhere('id', (int) $r->query('proveedor')) ?? $proveedores->first();
        return view('entregas.index', compact('entregas', 'proveedores', 'mes', 'total', 'litros', 'numero', 'seleccionado'));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => $this->reglaFecha(),
            'litros' => 'required|numeric|min:0.1',
            'precio_litro' => 'required|numeric|min:0',
        ], [
            'proveedor_id.required' => 'Selecciona un proveedor.',
            'litros.required' => 'Indica los litros entregados.',
            'precio_litro.required' => 'Indica el precio por litro.',
        ] + $this->mensajesFecha());
        $d['importe'] = round($d['litros'] * $d['precio_litro'], 2);
        Entrega::create($d);
        return back()->with('ok', 'Entrega registrada: S/ ' . number_format($d['importe'], 2));
    }

    public function update(Request $r, Entrega $entrega)
    {
        $d = $r->validate([
            'fecha' => $this->reglaFecha(),
            'litros' => 'required|numeric|min:0.1',
            'precio_litro' => 'required|numeric|min:0',
        ], $this->mensajesFecha());
        $d['importe'] = round($d['litros'] * $d['precio_litro'], 2);
        $p = Proveedor::withSum('entregas', 'importe')->withSum('pagos', 'monto')->find($entrega->proveedor_id);
        if ($p && ($p->saldo - (float) $entrega->importe + $d['importe']) < -0.005) {
            return back()->withErrors('Con ese importe el proveedor quedaría con saldo a favor: ya hay pagos que cubren esta entrega.');
        }
        $entrega->update($d);
        return back()->with('ok', 'Entrega actualizada: S/ ' . number_format($d['importe'], 2));
    }

    public function destroy(Entrega $entrega)
    {
        $p = Proveedor::withSum('entregas', 'importe')->withSum('pagos', 'monto')->find($entrega->proveedor_id);
        if ($p && ($p->saldo - (float) $entrega->importe) < -0.005) {
            return back()->withErrors('No se puede quitar: ya hay pagos que cubren esta entrega. Quita primero el pago.');
        }
        $entrega->delete();
        return back()->with('ok', 'Entrega eliminada');
    }
}
