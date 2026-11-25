<?php
namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    const OPCIONES = ['Transporte', 'Envases', 'Servicios', 'Insumos'];

    public function index(Request $r)
    {
        $mes = $this->mes($r);
        $q = Gasto::whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()]);
        $total = (clone $q)->sum('monto');
        $porGrupo = (clone $q)->selectRaw('categoria as grupo, SUM(monto) as total')->groupBy('categoria')->pluck('total', 'grupo');
        $registros = $q->latest('fecha')->latest('id')->paginate(12)->withQueryString();
        $opciones = self::OPCIONES;
        $cuadre = DashboardController::cuadre($mes);
        return view('gastos.index', compact('registros', 'mes', 'total', 'porGrupo', 'opciones', 'cuadre'));
    }

    private function reglas(): array
    {
        return [
            'fecha' => $this->reglaFecha(),
            'categoria' => 'required|in:' . implode(',', self::OPCIONES),
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:160',
        ];
    }

    public function store(Request $r)
    {
        Gasto::create($r->validate($this->reglas(), $this->mensajesFecha()));
        return back()->with('ok', 'Gasto registrado');
    }

    public function update(Request $r, Gasto $gasto)
    {
        $gasto->update($r->validate($this->reglas(), $this->mensajesFecha()));
        return back()->with('ok', 'Gasto actualizado');
    }

    public function destroy(Gasto $gasto)
    {
        $gasto->delete();
        return back()->with('ok', 'Registro eliminado');
    }
}
