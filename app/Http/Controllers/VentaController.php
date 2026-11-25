<?php
namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    const OPCIONES = ['Leche', 'Queso', 'Yogur'];

    private function reglas(): array
    {
        return [
            'fecha' => $this->reglaFecha(),
            'producto' => 'required|in:' . implode(',', self::OPCIONES),
            'monto' => 'required|numeric|min:0.01',
            'cantidad' => 'nullable|numeric|min:0',
        ];
    }

    public function index(Request $r)
    {
        $mes = $this->mes($r);
        $q = Venta::whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()]);
        $total = (clone $q)->sum('monto');
        $porGrupo = (clone $q)->selectRaw('producto as grupo, SUM(monto) as total')->groupBy('producto')->pluck('total', 'grupo');
        $registros = $q->latest('fecha')->latest('id')->paginate(12)->withQueryString();
        $opciones = self::OPCIONES;
        return view('ventas.index', compact('registros', 'mes', 'total', 'porGrupo', 'opciones'));
    }

    public function store(Request $r)
    {
        Venta::create($r->validate($this->reglas(), $this->mensajesFecha()));
        return back()->with('ok', 'Venta registrada');
    }

    public function update(Request $r, Venta $venta)
    {
        $venta->update($r->validate($this->reglas(), $this->mensajesFecha()));
        return back()->with('ok', 'Venta actualizada');
    }

    public function destroy(Venta $venta)
    {
        $venta->delete();
        return back()->with('ok', 'Venta eliminada');
    }
}
